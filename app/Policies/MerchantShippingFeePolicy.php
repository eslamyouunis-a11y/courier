<?php

namespace App\Policies;

use App\Models\MerchantShippingFee;
use App\Models\User;

class MerchantShippingFeePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MerchantShippingFee $merchantShippingFee): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // 👈 دي أهم واحدة، كانت false أو missing
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MerchantShippingFee $merchantShippingFee): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MerchantShippingFee $merchantShippingFee): bool
    {
        return true;
    }
}
