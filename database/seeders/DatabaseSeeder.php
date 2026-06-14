// ── Utilisateurs ──────────────────────────────────────
$patron = User::updateOrCreate(
    ['email' => 'patron@garage.local'],
    [
        'name'      => 'Thomas Patron',
        'phone'     => '+229 97 00 00 01',
        'role'      => 'patron',
        'password'  => Hash::make('password'),
        'is_active' => true,
    ]
);

$accueil = User::updateOrCreate(
    ['email' => 'accueil@garage.local'],
    [
        'name'      => 'Marie Accueil',
        'phone'     => '+229 97 00 00 02',
        'role'      => 'accueil',
        'password'  => Hash::make('password'),
        'is_active' => true,
    ]
);

$mec1 = User::updateOrCreate(
    ['email' => 'kofi@garage.local'],
    [
        'name'      => 'Kofi Mécanicien',
        'phone'     => '+229 97 00 00 03',
        'role'      => 'mecanicien',
        'password'  => Hash::make('password'),
        'is_active' => true,
    ]
);

$mec2 = User::updateOrCreate(
    ['email' => 'seydou@garage.local'],
    [
        'name'      => 'Seydou Alabi',
        'phone'     => '+229 97 00 00 04',
        'role'      => 'mecanicien',
        'password'  => Hash::make('password'),
        'is_active' => true,
    ]
);