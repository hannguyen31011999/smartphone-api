<?php

namespace App\Http\Controllers\api\frontend;

use Mail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetails;
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

    public function index(Request $request)
    {
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $this->client_ID,     // ClientID
                $this->client_Secret      // ClientSecret
            )
        );
        
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");
    
        $item1 = new Item();
        $item1->setName('Ground Coffee 40 oz')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setSku("123123") // Similar to `item_number` in Classic API
            ->setPrice(7.5);
        $item2 = new Item();
        $item2->setName('Granola bars')
            ->setCurrency('USD')
            ->setQuantity(5)
            ->setSku("321321") // Similar to `item_number` in Classic API
            ->setPrice(2);
    
        $itemList = new ItemList();
        $itemList->setItems(array($item1, $item2));
        
        $details = new Details();
        $details->setShipping(1.2)
            ->setTax(1.3)
            ->setSubtotal(17.50);
    
        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal(20)
            ->setDetails($details);
    
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Payment GridShop")
            ->setInvoiceNumber(uniqid());
    
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->Url_return)
                    ->setCancelUrl($this->Url_cancel);
    
        // Add NO SHIPPING OPTION
        $inputFields = new InputFields();
        $inputFields->setNoShipping(1);
    
        $webProfile = new WebProfile();
        $webProfile->setName('test' . uniqid())->setInputFields($inputFields);
    
        $webProfileId = $webProfile->create($apiContext)->getId();
    
        $payment = new Payment();
        $payment->setExperienceProfileId($webProfileId); // no shipping
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));
    
        try {
            $payment->create($apiContext);
        } catch (Exception $ex) {
            echo $ex;
            exit(1);
        }
    
        return $payment;
    }

    public function execute(Request $request)
    {
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $this->client_ID,     // ClientID
                $this->client_Secret      // ClientSecret
            )
        );
        $paymentId = $request->paymentId;
        $payment = Payment::get($paymentId, $apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($request->payerId);
        try {
            $result = $payment->execute($execution, $apiContext);
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'state'=>$result->state,
                'status'=>$result->payer->status
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

    public function paypalRedirect(Request $request)
    {
        if($request->all()){
            return response()->json([
                'status'=>$this->codeSuccess,
                'data'=>$request->all()
            ]);
        }
    }

    public function createOrder(Request $request)
    {
        $list = array();
        $input = [
            'user_id'=>$request->user_id,
            'transport_price'=>$request->transport_price,
            'order_note'=>$request->note ? $request->note : null,
            'order_email'=>$request->email,
            'order_name'=>$request->firstName . $request->lastName,
            'order_address'=>$request->address,
            'order_phone'=>$request->phone,
            'order_name'=>$request->firstName . $request->lastName,
            'order_payment'=>$request->payment,
            'payment_option'=>$request->paymentOption
        ];
        try {
            $input['order_status'] = 0;
            $order = Order::create($input);
            $apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                    $this->client_ID,     // ClientID
                    $this->client_Secret      // ClientSecret
                )
            );
            $itemList = new ItemList();
            $details = new Details();
            $payer = new Payer();
            $amount = new Amount();
            $transaction = new Transaction();
            $redirectUrls = new RedirectUrls();
            $inputFields = new InputFields();
            $webProfile = new WebProfile();
            $payment = new Payment();
            foreach ($request->cart as $cart) {
                $discount = (int)$cart['discount'] ? (int)$cart['discount'] : 0;
                $result = $order->OrderDetails()->create([
                    'sku_id'=>$cart['sku_id'],
                    'product_name'=>$cart['name'],
                    'product_price'=>$cart['promotion_price'] ? $cart['promotion_price'] : $cart['unit_price'],
                    'qty'=>$cart['qty'],
                    'discount'=>$cart['qty']
                ]);
                $price = (int)$result->product_price - (int)$discount;
                $item = new Item();
                $item->setName($cart['name'])
                    ->setCurrency('USD')
                    ->setQuantity((int)$cart['qty'])
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
                $webProfileId = $webProfile->create($apiContext)->getId();
                $payment->setExperienceProfileId($webProfileId); // no shipping
                $payment->setIntent("sale")
                    ->setPayer($payer)
                    ->setRedirectUrls($redirectUrls)
                    ->setTransactions(array($transaction));
                try {
                    $payment->create($apiContext);
                    $checkoutUrl = null;
                    foreach ($payment->getLinks() as $link) {
                        if ($link->getRel() == 'approval_url') {
                            $checkoutUrl = $link->getHref();
                            break;
                        }
                    }
                    return response()->json([
                        'status_code'=>$this->codeSuccess,
                        'data'=>$checkoutUrl
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

    public function updateStatusOrder(Request $request,$id)
    {
        $user = User::findOrFail($id);
        $order = $user->Orders()->latest()->first();
        if($request->status == "update"){
            $order->update(['order_status'=>2]);
            $cart = $user->Carts()->get();
            if(!empty($cart)){
                foreach($cart as $value){
                    $product = $value->ProductSkus()->first();
                    $product->InventoryManagements()->update([
                        'qty'=>$product->sku_qty - $value->qty
                    ]);
                    $product->update([
                        'sku_qty'=>$product->sku_qty - $value->qty
                    ]);
                    $value->delete();
                }
            }
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'message'=>"Payment success"
            ]);
        }else {
            $order->OrderDetails()->delete();
            $order->delete();
        }
    }
}