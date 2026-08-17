<?php
$m = \App\Models\User::create([
    'name' => 'MARLINDA OCTAVIANI', 
    'username' => 'marlinda', 
    'email' => 'marlinda@example.com',
    'password' => bcrypt('marlinda123'), 
    'role' => 'investor'
]);
\App\Models\Investor::where('id', 2)->update(['user_id' => $m->id]);

$a = \App\Models\User::create([
    'name' => 'MUHAMAD ALDIANSYAH', 
    'username' => 'aldiansyah', 
    'email' => 'aldiansyah@example.com',
    'password' => bcrypt('aldiansyah123'), 
    'role' => 'investor'
]);
\App\Models\Investor::where('id', 3)->update(['user_id' => $a->id]);

echo "Success!";
