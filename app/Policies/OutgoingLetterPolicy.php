<?php

namespace App\Policies;

use App\Models\OutgoingLetter;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OutgoingLetterPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'pimpinan']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OutgoingLetter $outgoingLetter): bool
    {
        return in_array($user->role, ['admin', 'pimpinan']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OutgoingLetter $outgoingLetter): bool
    {
        return $user->role === 'admin' && $outgoingLetter->status !== 'completed';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OutgoingLetter $outgoingLetter): bool
    {
        return $user->role === 'admin' && $outgoingLetter->status !== 'completed';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OutgoingLetter $outgoingLetter): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OutgoingLetter $outgoingLetter): bool
    {
        return false;
    }
}
