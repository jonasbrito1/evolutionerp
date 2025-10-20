<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrador com acesso total ao sistema',
                'permissions' => [
                    'users.create', 'users.read', 'users.update', 'users.delete',
                    'roles.manage', 'permissions.manage',
                    'edicts.create', 'edicts.read', 'edicts.update', 'edicts.delete',
                    'tenders.create', 'tenders.read', 'tenders.update', 'tenders.delete',
                    'documents.create', 'documents.read', 'documents.update', 'documents.delete',
                    'finances.read', 'finances.update',
                    'reports.read', 'audit.read'
                ]
            ],
            [
                'name' => 'manager',
                'description' => 'Gerenciador de Licitações',
                'permissions' => [
                    'edicts.create', 'edicts.read', 'edicts.update',
                    'tenders.create', 'tenders.read', 'tenders.update',
                    'documents.create', 'documents.read', 'documents.update',
                    'services.read', 'suppliers.read',
                    'reports.read'
                ]
            ],
            [
                'name' => 'financial',
                'description' => 'Gestor Financeiro',
                'permissions' => [
                    'edicts.read', 'tenders.read',
                    'finances.create', 'finances.read', 'finances.update',
                    'reports.read'
                ]
            ],
            [
                'name' => 'consultant',
                'description' => 'Consultor (Visualização Apenas)',
                'permissions' => [
                    'edicts.read', 'tenders.read',
                    'documents.read', 'services.read',
                    'suppliers.read', 'reports.read'
                ]
            ],
            [
                'name' => 'support',
                'description' => 'Suporte e Administrativo',
                'permissions' => [
                    'documents.create', 'documents.read', 'documents.update',
                    'users.read', 'suppliers.read',
                    'reports.read'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }
    }
}
