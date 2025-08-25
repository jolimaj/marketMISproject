<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "role_id" => 3,
            "gender_id" => 1,
            "first_name" => $this->faker->firstName(),
            "last_name" => $this->faker->lastName(),
            "address" => $this->faker->address(),
            "mobile" => "639" . $this->faker->numerify("#########"),
            "birthday" => Carbon::now()
                ->subYears(20)
                ->format("Y-m-d"),
            "email" => $this->faker->unique()->safeEmail(),
            "email_verified_at" => now(),
            "password" => Hash::make("marketis"),
            "two_factor_secret" => null,
            "two_factor_recovery_codes" => null,
            "remember_token" => Str::random(10),
            "profile_photo_path" => null,
        ];
    }

    /**
     * Custom static seed users
     */
    public static function seedDefaultUsers()
    {
        $users = [
            [
                "role_id" => 1,
                "gender_id" => 3,
                "first_name" => "Admin",
                "last_name" => "MarketIS",
                "address" => "Sariaya, Quezon",
                "mobile" => "63916625711",
                "birthday" => Carbon::now()
                    ->subYears(20)
                    ->format("Y-m-d"),
                "email" => "marketchristian7@gmail.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
            ],
            [
                "role_id" => 2,
                "gender_id" => 2, // Male
                "department_id" => 1,
                "sub_admin_type_id" => 1,
                "first_name" => "John",
                "last_name" => "Doe",
                "address" => "Lucena City, Quezon",
                "mobile" => "639111111111",
                "birthday" => Carbon::now()
                    ->subYears(28)
                    ->format("Y-m-d"),
                "email" => "john.doe@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
            [
                "role_id" => 2,
                "gender_id" => 1, // Female
                "department_id" => 2,
                "sub_admin_type_id" => 2,
                "first_name" => "Jane",
                "last_name" => "Smith",
                "address" => "Tayabas, Quezon",
                "mobile" => "639122222222",
                "birthday" => Carbon::now()
                    ->subYears(25)
                    ->format("Y-m-d"),
                "email" => "jane.smith@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
            [
                "role_id" => 2,
                "gender_id" => 2,
                "department_id" => 3,
                "sub_admin_type_id" => 3,
                "first_name" => "Carlos",
                "last_name" => "Reyes",
                "address" => "Sariaya, Quezon",
                "mobile" => "639133333333",
                "birthday" => Carbon::now()
                    ->subYears(32)
                    ->format("Y-m-d"),
                "email" => "carlos.reyes@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
            [
                "role_id" => 2,
                "gender_id" => 1,
                "department_id" => 4,
                "sub_admin_type_id" => 4,
                "first_name" => "Maria",
                "last_name" => "Santos",
                "address" => "Pagbilao, Quezon",
                "mobile" => "639144444444",
                "birthday" => Carbon::now()
                    ->subYears(29)
                    ->format("Y-m-d"),
                "email" => "maria.santos@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
            [
                "role_id" => 2,
                "gender_id" => 2,
                "department_id" => 5,
                "sub_admin_type_id" => 5,
                "first_name" => "Miguel",
                "last_name" => "Torres",
                "address" => "Lopez, Quezon",
                "mobile" => "639155555555",
                "birthday" => Carbon::now()
                    ->subYears(35)
                    ->format("Y-m-d"),
                "email" => "miguel.torres@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
            [
                "role_id" => 2,
                "gender_id" => 1,
                "department_id" => 6,
                "sub_admin_type_id" => 6,
                "first_name" => "Angela",
                "last_name" => "Dela Cruz",
                "address" => "Candelaria, Quezon",
                "mobile" => "639166666666",
                "birthday" => Carbon::now()
                    ->subYears(24)
                    ->format("Y-m-d"),
                "email" => "angela.delacruz@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
            [
                "role_id" => 2,
                "gender_id" => 2,
                "department_id" => 7,
                "sub_admin_type_id" => 7,
                "first_name" => "Joseph",
                "last_name" => "Cruz",
                "address" => "Atimonan, Quezon",
                "mobile" => "639177777777",
                "birthday" => Carbon::now()
                    ->subYears(40)
                    ->format("Y-m-d"),
                "email" => "joseph.cruz@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
            [
                "role_id" => 2,
                "gender_id" => 1,
                "department_id" => 8,
                "sub_admin_type_id" => 8,
                "first_name" => "Elena",
                "last_name" => "Flores",
                "address" => "Unisan, Quezon",
                "mobile" => "639188888888",
                "birthday" => Carbon::now()
                    ->subYears(27)
                    ->format("Y-m-d"),
                "email" => "elena.flores@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
            [
                "role_id" => 2,
                "gender_id" => 2,
                "department_id" => 9,
                "sub_admin_type_id" => 9,
                "first_name" => "Ricardo",
                "last_name" => "Villanueva",
                "address" => "Gumaca, Quezon",
                "mobile" => "639199999999",
                "birthday" => Carbon::now()
                    ->subYears(38)
                    ->format("Y-m-d"),
                "email" => "ricardo.villanueva@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
            [
                "role_id" => 2,
                "gender_id" => 1,
                "department_id" => 10,
                "sub_admin_type_id" => 10,
                "first_name" => "Isabella",
                "last_name" => "Garcia",
                "address" => "Calauag, Quezon",
                "mobile" => "639101010101",
                "birthday" => Carbon::now()
                    ->subYears(26)
                    ->format("Y-m-d"),
                "email" => "isabella.garcia@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
            [
                "role_id" => 2,
                "gender_id" => 2,
                "department_id" => 11,
                "sub_admin_type_id" => 11,
                "first_name" => "Francis",
                "last_name" => "Lopez",
                "address" => "Tagkawayan, Quezon",
                "mobile" => "639111111112",
                "birthday" => Carbon::now()
                    ->subYears(31)
                    ->format("Y-m-d"),
                "email" => "francis.lopez@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
            [
                "role_id" => 2,
                "gender_id" => 1,
                "department_id" => 12,
                "sub_admin_type_id" => 12,
                "first_name" => "Sofia",
                "last_name" => "Mendoza",
                "address" => "San Narciso, Quezon",
                "mobile" => "639122222223",
                "birthday" => Carbon::now()
                    ->subYears(30)
                    ->format("Y-m-d"),
                "email" => "sofia.mendoza@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],

            [
                "role_id" => 3,
                "gender_id" => 1,
                "first_name" => "John",
                "last_name" => "Doe",
                "address" => "Sariaya, Quezon",
                "mobile" => "63916625711",
                "birthday" => Carbon::now()
                    ->subYears(20)
                    ->format("Y-m-d"),
                "email" => "johndoe@marketis.com",
                "email_verified_at" => now(),
                "password" => Hash::make("marketis"),
                "two_factor_secret" => null,
                "two_factor_recovery_codes" => null,
                "remember_token" => Str::random(10),
                "profile_photo_path" => null,
                "status" => true,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(["email" => $user["email"]], $user);
        }
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "email_verified_at" => null,
            ]
        );
    }

    /**
     * Indicate that the user should have a personal team.
     */
    public function withPersonalTeam(?callable $callback = null): static
    {
        if (!Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(
                    fn(array $attributes, User $user) => [
                        "name" => $user->name . '\'s Team',
                        "user_id" => $user->id,
                        "personal_team" => true,
                    ]
                )
                ->when(is_callable($callback), $callback),
            "ownedTeams"
        );
    }
}
