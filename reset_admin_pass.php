<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@fonex.com')->first();
if ($user) {
    $user->password = Hash::make('admin123');
    $user->save();
    echo "Admin password updated successfully.\n";
} else {
    echo "Admin user not found.\n";
}
