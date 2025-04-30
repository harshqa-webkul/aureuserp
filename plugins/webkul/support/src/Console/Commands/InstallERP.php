<?php

namespace Webkul\Support\Console\Commands;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Webkul\Support\Models\Company;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class InstallERP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'erp:install 
                             {--no-interaction : Run the command without prompting for input}
                             {--skip-env-check : Skip environment checks during installation}
                             {--skip-admin-creation : Skip admin user creation during installation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the ERP system with Filament and Filament Shield';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->option('skip-env-check')) {
            $this->checkEnvironment();
        }

        $this->info('🚀 Starting ERP System Installation...');

        $this->runMigrations();

        $this->generateRolesAndPermissions();

        $this->storageLink();

        $this->runSeeder();

        if (! $this->option('skip-admin-creation')) {
            $this->createAdminUser();
        }

        $this->info('🎉 ERP System installation completed successfully!');
    }

    /**
     * Run database migrations.
     */
    protected function runMigrations(): void
    {
        $this->info('⚙️ Running database migrations...');

        Artisan::call('migrate', [], $this->getOutput());

        $this->info('✅ Migrations completed successfully.');
    }

    /**
     * Run database seeders.
     */
    protected function runSeeder()
    {
        $this->info('⚙️ Running database seeders...');

        Artisan::call('db:seed', [], $this->getOutput());

        $this->info('✅ Seeders completed successfully.');
    }

    /**
     * Generate roles and permissions using Filament Shield.
     */
    protected function generateRolesAndPermissions(): void
{
    $this->info('🛡 Generating roles and permissions...');

    if ($this->option('no-interaction')) {
        $roleName = 'Admin'; // Default value for non-interactive mode
    } else {
        $roleName = $this->getAdminRoleName();
    }

    $adminRole = Role::firstOrCreate(['name' => $roleName]);

    Artisan::call('shield:generate', ['--all' => true, '--option' => 'permissions'], $this->getOutput());

    $permissions = Permission::all();
    $adminRole->syncPermissions($permissions);

    $this->info('✅ Roles and permissions generated and assigned successfully.');
}

/**
 * Create the initial Admin user with the Super Admin role.
 */
protected function createAdminUser(): void
{
    $this->info('👤 Creating an Admin user...');

    $defaultCompany = Company::first();

    $userModel = app(config('filament-shield.auth_provider_model.fqcn'));

    if ($this->option('no-interaction')) {
        // Use default values in non-interactive mode
        $adminData = [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Use a secure default password in production
            'resource_permission' => 'global',
            'default_company_id' => $defaultCompany->id,
        ];
    } else {
        // Interactive mode
        $adminData = [
            'name' => text(
                'Name',
                default: 'Admin',
                required: true
            ),
            'email' => text(
                'Email address',
                default: 'admin@example.com',
                required: true,
                validate: fn ($email) => $this->validateAdminEmail($email, $userModel)
            ),
            'password' => Hash::make(
                password(
                    'Password',
                    required: true,
                    validate: fn ($value) => $this->validateAdminPassword($value)
                )
            ),
            'resource_permission' => 'global',
            'default_company_id' => $defaultCompany->id,
        ];
    }

    $adminUser = $userModel::updateOrCreate(['email' => $adminData['email']], $adminData);

    $defaultCompany->update(['creator_id' => $adminUser->id]);

    $adminRoleName = $this->getAdminRoleName();

    if (! $adminUser->hasRole($adminRoleName)) {
        $adminUser->assignRole($adminRoleName);
    }

    $this->info("✅ Admin user '{$adminUser->name}' created and assigned the '{$this->getAdminRoleName()}' role successfully.");
}


    /**
     * Retrieve the Super Admin role name from the configuration.
     */
    protected function getAdminRoleName(): string
    {
        return Utils::getPanelUserRoleName();
    }

    /**
     * Validate the provided admin email.
     */
    protected function validateAdminEmail(string $email, Model $userModel): ?string
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'The email address must be valid.';
        }

        if ($userModel::where('email', $email)->exists()) {
            return 'A user with this email address already exists.';
        }

        return null;
    }

    /**
     * Validate the provided admin password.
     */
    protected function validateAdminPassword(string $password): ?string
    {
        return strlen($password) >= 8 ? null : 'The password must be at least 8 characters long.';
    }

    protected function askToStarGithubRepository(): void
    {
        if (! $this->confirm('Would you like to star our repo on GitHub?')) {
            return;
        }

        $repoUrl = 'https://github.com/aureuserp/aureuserp';

        if (PHP_OS_FAMILY == 'Darwin') {
            exec("open {$repoUrl}");
        }

        if (PHP_OS_FAMILY == 'Windows') {
            exec("start {$repoUrl}");
        }

        if (PHP_OS_FAMILY == 'Linux') {
            exec("xdg-open {$repoUrl}");
        }
    }

    private function storageLink()
    {
        if (file_exists(public_path('storage'))) {
            return;
        }

        $this->info('🔗 Linking storage directory...');

        Artisan::call('storage:link', [], $this->getOutput());

        $this->info('✅ Storage directory linked successfully.');
    }
}
