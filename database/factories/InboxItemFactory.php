<?php

namespace Database\Factories;

use App\Models\InboxItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InboxItemFactory extends Factory
{
    protected $model = InboxItem::class;

    public function definition(): array
    {
        $kinds = [
            'lead-received', 'lead-accepted', 'lead-rejected',
            'lead-converted', 'lead-reminder', 'message',
            'network', 'connection', 'deadline',
        ];
        $kind = $this->faker->randomElement($kinds);
        $typeMap = [
            'lead-received'  => 'action',
            'lead-accepted'  => 'info',
            'lead-rejected'  => 'info',
            'lead-converted' => 'info',
            'lead-reminder'  => 'alert',
            'message'        => 'message',
            'network'        => 'info',
            'connection'     => 'info',
            'deadline'       => 'alert',
        ];

        return [
            'kind'          => $kind,
            'type'          => $typeMap[$kind],
            'read'          => $this->faker->boolean(40),
            'archived'      => false,
            'ts'            => $this->faker->dateTimeBetween('-7 days', 'now'),
            'actor_name'    => $this->faker->name(),
            'actor_title'   => $this->faker->jobTitle(),
            'actor_company' => $this->faker->company(),
            'lead_ref'      => in_array($kind, ['lead-received', 'lead-accepted', 'lead-rejected', 'lead-converted', 'lead-reminder', 'deadline'])
                ? 'L-' . $this->faker->numberBetween(100, 999)
                : null,
            'title'         => $this->faker->sentence(5),
            'preview'       => $this->faker->sentence(10),
            'body'          => $this->faker->paragraphs(2, true),
            'user_id'       => User::factory(),
        ];
    }
}
