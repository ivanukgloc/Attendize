<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Organiser;

class OrganiserTest extends TestCase
{
    public function test_create_organiser_is_successful()
    {
        $this->actingAs($this->test_user)
            ->visit(route('showCreateOrganiser'))
            ->type($this->faker->name, 'name')
            ->type($this->faker->email, 'email')
            ->type($this->faker->email, 'about')
            ->type($this->faker->word, 'facebook')
            ->type($this->faker->word, 'twitter')
            ->press('Create Organiser')
            ->seeJson([
                'status' => 'success'
            ]);
    }
}
