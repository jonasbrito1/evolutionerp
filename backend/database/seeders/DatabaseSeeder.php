<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Criar Roles
        $this->call(RoleSeeder::class);

        // Criar Company Padrão
        $company = Company::create([
            'name' => 'LicitaEvolution Demo',
            'cnpj' => '00.000.000/0000-00',
            'industry' => 'Serviços',
            'founding_date' => now()->subYears(5),
            'address' => 'Rua Principal, 123 - São Paulo, SP',
            'phone' => '(11) 9999-9999',
            'email' => 'contato@licitaevolution.local',
            'description' => 'Empresa Demo para Testes do LicitaEvolution',
            'status' => 'active',
        ]);

        // Criar Admin User
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();

        User::create([
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
            'name' => 'Administrador',
            'email' => 'admin@licitaevolution.local',
            'password' => Hash::make('admin123456'),
            'department' => 'TI',
            'status' => 'active',
        ]);

        User::create([
            'company_id' => $company->id,
            'role_id' => $managerRole->id,
            'name' => 'Gerenciador de Licitações',
            'email' => 'gerente@licitaevolution.local',
            'password' => Hash::make('gerente123456'),
            'department' => 'Comercial',
            'status' => 'active',
        ]);
    }
}
