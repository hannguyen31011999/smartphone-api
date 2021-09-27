<?php
namespace App\Http\Controllers\api\frontend;
use Mail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Order;
use App\Models\Cart;
use App\Models\OrderDetails;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Item;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\ItemList;
use PayPal\Api\WebProfile;
use PayPal\Api\InputFields;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\PaymentExecution;

class ApiCheckoutController extends Controller
{
    private $Url_return = "http://localhost:3000/checkout";
    private $Url_cancel = "http://localhost:3000/checkout";
    private $client_ID = "AdTG5HIyXFQMpOtgR-8DEeU8q87le9OohEE2acrzVujJm2NAZ9N5-Q7Jls9OiQOqDBkoT5KSdljJN4B8";
    private $client_Secret = "EPT-BonuRKBaOxTVeOKjrNc2gEgLgoc0f9cjWwl22cjNYnb3R_CiFQLgFAlyE4jx4MF9rZDq8b1H0eIt";
    private $apiContext;
    
    public function __construct()
    {
        $paypal_configuration = \Config::get('paypal');
        $this->apiContext = new ApiContext(new OAuthTokenCredential($this->client_ID, $this->client_Secret));
        $this->apiContext->setConfig($paypal_configuration['settings']);
    }


    public function createOrderWithPayPal(Request $request)
    {
        $list = array();
        $input = [
            'user_id'=>$request->user_id,
            'transport_price'=>$request->transport_price,
            'order_note'=>$request->note ? $request->note : null,
            'order_email'=>$request->email,
            'order_name'=>$request->firstName." ".$request->lastName,
            'order_address'=>$request->address,
            'order_phone'=>$request->phone,
            'order_name'=>$request->firstName . $request->lastName,
            'order_payment'=>$request->payment,
            'payment_option'=>$request->paymentOption
        ];
        try {
            $input['order_status'] = 0;
            $order = Order::create($input);
            $itemList = new ItemList();
            $details = new Details();
            $payer = new Payer();
            $amount = new Amount();
            $transaction = new Transaction();
            $redirectUrls = new RedirectUrls();
            $inputFields = new InputFields();
            $webProfile = new WebProfile();
            $payment = new Payment();
            foreach ($request->cart as $value) {
                $cart = Cart::findOrFail($value);
                $result = $order->OrderDetails()->create([
                    'sku_id'=>$cart->sku_id,
                    'product_name'=>$cart->name,
                    'product_price'=>$cart->promotion_price ? $cart->promotion_price : $cart->unit_price,
                    'qty'=>$cart->qty,
                    'discount'=>$cart->discount
                ]);
                $price = (int)$result->product_price - (int)$result->discount;
                $item = new Item();
                $item->setName($cart->name)
                    ->setCurrency('USD')
                    ->setQuantity((int)$cart->qty)
                    ->setSku((string)$result->id)
                    ->setPrice($price);
                array_push($list,$item);
            }
            if((int)$request->payment == 2){
                $payer->setPaymentMethod("paypal");
                $itemList->setItems($list);
                $details->setShipping(intval($request->transport_price))
                        ->setSubtotal(intval($request->totalPrice) - intval($request->transport_price));
                $amount->setCurrency("USD")
                    ->setTotal(intval($request->totalPrice))
                    ->setDetails($details);
                $transaction->setAmount($amount)
                    ->setItemList($itemList)
                    ->setDescription("Payment GridShop")
                    ->setInvoiceNumber(uniqid());
                $redirectUrls->setReturnUrl($this->Url_return)
                            ->setCancelUrl($this->Url_cancel);
                // Add NO SHIPPING OPTION
                $inputFields->setNoShipping(1);
                $webProfile->setName('test' . uniqid())->setInputFields($inputFields);
                $webProfileId = $webProfile->create($this->apiContext)->getId();
                $payment->setExperienceProfileId($webProfileId); // no shipping
                $payment->setIntent("sale")
                    ->setPayer($payer)
                    ->setRedirectUrls($redirectUrls)
                    ->setTransactions(array($transaction));
                try {
                    $payment->create($this->apiContext);
                    $checkoutUrl = null;
                    foreach ($payment->getLinks() as $link) {
                        if ($link->getRel() == 'approval_url') {
                            $checkoutUrl = $link->getHref();
                            break;
                        }
                    }
                    return response()->json([
                        'status_code'=>$this->codeSuccess,
                        'redirect'=>$checkoutUrl,
                        'order_id'=>$order->id
                    ]);
                } catch (Exception $ex) {
                    return response()->json([
                        'status_code' => $this->codeFails,
                        'message' => 'Server error not response'
                    ],$this->codeFails);
                }
            }else {
                if($order){
                    $user = User::findOrFail($input['user_id']);
                    if($user->Carts()->delete()){
                        return response()->json([
                            'status_code'=>$this->codeSuccess,
                            'message'=>"Checkout success"
                        ]);
                    }
                }else {
                    return response()->json([
                        'status_code'=>$this->codeFails,
                        'message'=>"Checkout failed"
                    ]);
                }
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        } 
    }

    public function execute(Request $request)
    {
        $order = null;
        $paymentId = $request->paymentId;
        $payment = Payment::get($paymentId, $this->apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($request->payerId);
        try {
            $result = $payment->execute($execution, $this->apiContext);
            if($result->state == "approved" && $result->payer->status == "VERIFIED"){
                if(count($request->cart) > 0){
                    $user = User::findOrFail($request->user_id);
                    $order = Order::findOrFail($request->order_id);
                    $order->update(['order_status'=>2]);
                    foreach($request->cart as $value){
                        $cart = Cart::findOrFail($value);
                        $product = $cart->ProductSkus()->first();
                        $product->InventoryManagements()->update([
                            'qty'=>$product->sku_qty - $cart->qty
                        ]);
                        $product->update([
                            'sku_qty'=>$product->sku_qty - $cart->qty
                        ]);
                        $cart->delete();
                    }
                }
            }else {
                return response()->json([
                    'status'=>$this->codeFails,
                    'message'=>'Sever errors',
                ],$this->codeFails);
            }
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'state'=>$result->state,
                'status'=>$result->payer->status,
                'data'=>$order->with(['OrderDetails' => function($query){
                    $query->with('ProductSkus')->get();
                }])->latest()->first()
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'status'=>$this->codeFails,
                'message'=>'Sever errors',
            ],$this->codeFails);
        }
    }

    public function cancelPaypal(Request $request)
    {
        if($request->all()){
            return response()->json([
                'status'=>$this->codeSuccess,
                'message'=>'Payment failed'
            ]);
        }
    }

    public function updateStatusOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->OrderDetails()->delete();
        $order->delete();
        return response()->json([
            'status_code'=>$this->codeSuccess,
            'message'=>"Sorry, Payment failed"
        ]);
    }

    public function createOrderDirect(Request $request)
    {
        $input = [
            'user_id'=>$request->user_id,
            'transport_price'=>$request->transport_price,
            'order_note'=>$request->note ? $request->note : null,
            'order_email'=>$request->email,
            'order_name'=>$request->firstName." ".$request->lastName,
            'order_address'=>$request->address,
            'order_phone'=>$request->phone,
            'order_name'=>$request->firstName . $request->lastName,
            'order_payment'=>$request->payment,
            'payment_option'=>$request->paymentOption,
            'order_status'=>1
        ];
        try {
            $order = Order::create($input);
            foreach ($request->cart as $value) {
                $cart = Cart::findOrFail($value);
                $price = $cart->promotion_price ? (int)$cart->promotion_price : (int)$cart->unit_price;
                $result = $order->OrderDetails()->create([
                    'sku_id'=>$cart->sku_id,
                    'product_name'=>$cart->name,
                    'product_price'=>$price - (int)$cart->discount,
                    'qty'=>$cart->qty,
                    'discount'=>$cart->discount
                ]);
                $cart->delete();
            }
            return response()->json([
                'status'=>$this->codeSuccess,
                'data'=>$order->with(['OrderDetails' => function($query){
                    $query->with('ProductSkus')->get();
                }])->latest()->first()
            ]);
        }catch(Exception $e){
            return response()->json([
                'status'=>$this->codeFails,
                'message'=>'Checkout failed'
            ],$this->codeFails);
        }
    }
}