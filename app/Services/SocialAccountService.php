<?php

namespace App\Services;

use Laravel\Socialite\Contracts\User as ProviderUser;
use App\Models\SocialAccount;
use App\Models\User;
use Hash;

class SocialAccountService
{
    public static function createOrGetUser(ProviderUser $providerUser,$social)
    {
        $account = SocialAccount::whereProvider($social)
            ->whereProviderUserId($providerUser->getId())
            ->first();
        if ($account) {
            return $account->Users()->first();
        } else {
            $email = $providerUser->getEmail() ?? $providerUser->getNickname();
            $account = new SocialAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => 'facebook'
            ]);
            $user = User::whereEmail($email)->first();
            if (!$user) {
                $user = User::create([
                    'email' => $email,
                    'name' => $providerUser->getName(),
                    'password' => Hash::make($providerUser->getName()),
                    'phone' => '0382484047',
                    'address' => 'Địa chỉ của'.$providerUser->getName(),
                    'status' => 0,
                    'role' => 1
                ]);
            }
            $account->Users()->associate($user);
            $account->save();
            return $user;
        }
    }
}