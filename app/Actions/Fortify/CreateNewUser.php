<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\OnboardingSession;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ContentWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user with a draft vendor + in-progress
     * onboarding session. The actual business name + locale are filled by the AI agent
     * during onboarding; until then the user's name is used as a placeholder.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => 'vendor',
            ]);

            $vendor = Vendor::create([
                'user_id' => $user->id,
                'slug' => $this->generateUniqueSlug($input['name']),
                'name' => $input['name'],
                'status' => 'draft',
                'locale' => 'th',
            ]);

            OnboardingSession::create([
                'vendor_id' => $vendor->id,
                'status' => 'in_progress',
            ]);

            app(ContentWriter::class)->initializeVendor($vendor);

            return $user;
        });
    }

    /**
     * Build a kebab-case slug from the user's name, with a short random suffix
     * to avoid collisions and to handle non-ASCII names (Thai) that strip to empty.
     */
    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'vendor';

        do {
            $candidate = $base.'-'.Str::lower(Str::random(4));
        } while (Vendor::where('slug', $candidate)->exists());

        return $candidate;
    }
}
