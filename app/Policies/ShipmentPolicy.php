<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    /**
     * Determine whether the user can view any shipments.
     */
    public function viewAny(User $user): bool
    {
        return true; // List is scoped by role in controller/query
    }

    /**
     * Determine whether the user can view the specific shipment.
     * Rule: Admin & Operator can view all; Customer can ONLY view their own.
     */
    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->isAdmin() || $user->isOperator()) {
            return true;
        }

        return $shipment->customer_id === $user->id;
    }

    /**
     * Determine whether the user can create shipments.
     * Rule: Admin & Operator only.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can update the shipment.
     * Rule: Admin & Operator only.
     */
    public function update(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can delete the shipment.
     * Rule: Admin only.
     */
    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can transition the shipment status.
     * Rule: Admin & Operator only.
     */
    public function transition(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can add tracking events.
     * Rule: Admin & Operator only.
     */
    public function addTracking(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can raise or resolve exceptions.
     * Rule: Admin & Operator only.
     */
    public function manageExceptions(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }
}
