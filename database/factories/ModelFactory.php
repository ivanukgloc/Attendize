<?php


/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use Carbon\Carbon;

$factory->define(App\OrderStatus::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text,
    ];
});

$factory->define(App\TicketStatus::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text,
    ];
});

$factory->define(App\ReservedTickets::class, function (Faker\Generator $faker) {
    return [
        'ticket_id'         => factory(App\Ticket::class)->create()->id,
        'event_id'          => factory(App\Event::class)->create()->id,
        'quantity_reserved' => 50,
        'expires'           => Carbon::now()->addDays(2),
        'session_id'        => $faker->randomNumber
    ];
});

$factory->define(App\Timezone::class, function (Faker\Generator $faker) {
    return [
        'name'     => 'America/New_York',
        'location' => 'New York'
    ];
});


$factory->define(App\DateFormat::class, function (Faker\Generator $faker) {
    return [
        'format'        => "Y-m-d",
        'picker_format' => "Y-m-d",
        'label'         => "utc date",
    ];
});


$factory->define(App\DateTimeFormat::class, function (Faker\Generator $faker) {
    return [
        'format'        => "Y-m-d H:i:s",
        'picker_format' => "Y-m-d H:i:s",
        'label'         => "utc",
    ];
});

$factory->define(App\Currency::class, function (Faker\Generator $faker) {
    return [
        'title'          => "Dollar",
        'symbol_left'    => "$",
        'symbol_right'   => "",
        'code'           => 'USD',
        'decimal_place'  => 2,
        'value'          => 100.00,
        'decimal_point'  => '.',
        'thousand_point' => ',',
        'status'         => 1
    ];
});

//TODO create country class so country_id can be populated
$factory->define(App\Account::class, function (Faker\Generator $faker) {
    return [
        'first_name'             => $faker->firstName,
        'last_name'              => $faker->lastName,
        'email'                  => $faker->email,
        'timezone_id'            => factory(App\Timezone::class)->create()->id,
        'date_format_id'         => factory(App\DateFormat::class)->create()->id,
        'datetime_format_id'     => factory(App\DateTimeFormat::class)->create()->id,
        'currency_id'            => factory(App\Currency::class)->create()->id,
        'name'                   => $faker->name,
        'last_ip'                => "127.0.0.1",
        'last_login_date'        => Carbon::now()->subDays(2),
        'address1'               => $faker->address,
        'address2'               => "",
        'city'                   => $faker->city,
        'state'                  => $faker->stateAbbr,
        'postal_code'            => $faker->postcode,
//        'country_id'             => factory(App\Country::class)->create()->id,
        'email_footer'           => 'Email footer text',
        'is_active'              => false,
        'is_banned'              => false,
        'is_beta'                => false,
        'stripe_access_token'    => str_random(10),
        'stripe_refresh_token'   => str_random(10),
        'stripe_secret_key'      => str_random(10),
        'stripe_publishable_key' => str_random(10),

    ];
});

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'account_id'        => factory(App\Account::class)->create()->id,
        'first_name'        => $faker->firstName,
        'last_name'         => $faker->lastName,
        'phone'             => $faker->phoneNumber,
        'email'             => $faker->email,
        'password'          => $faker->password,
        'is_admin'          => 0,
        'confirmation_code' => $faker->randomNumber,
        'is_registered'     => false,
        'is_confirmed'      => false,
        'is_parent'         => false,
        'remember_token'    => $faker->randomNumber
    ];
});

$factory->defineAs(App\User::class, 'admin', function ($faker) use ($factory) {
    $user = $factory->raw(App\User::class);

    return array_merge($user, ['is_admin' => 1]);
});

$factory->define(App\Organiser::class, function (Faker\Generator $faker) {
    return [
        'account_id'         => factory(App\Account::class)->create()->id,
        'name'               => $faker->name,
        'about'              => $faker->text,
        'email'              => $faker->email,
        'phone'              => $faker->phoneNumber,
        'facebook'           => 'https://facebook.com/organizer-profile',
        'twitter'            => 'https://twitter.com/organizer-profile',
        'logo_path'          => 'path/to/logo',
        'is_email_confirmed' => 0,
        'confirmation_key'   => str_random(15),
    ];
});

$factory->define(App\Event::class, function (Faker\Generator $faker) {
    return [
        'title'                      => $faker->name,
        'location'                   => $faker->text,
        'bg_type'                    => 'color',
        'bg_color'                   => config('attendize.event_default_bg_color'),
        'bg_image_path'              => $faker->imageUrl,
        'description'                => $faker->text,
        'start_date'                 => Carbon::now(),
        'end_date'                   => Carbon::now()->addDay(),
        'on_sale_date'               => Carbon::now()->subDays(20),
        'account_id'                 => factory(App\Account::class)->create()->id,
        'user_id'                    => factory(App\User::class)->create()->id,
        'currency_id'                => factory(App\Currency::class)->create()->id,
        'sales_volume'               => 0,
        'organiser_fees_volume'      => 0,
        'organiser_fee_fixed'        => 0,
        'organiser_fee_percentage'   => 0,
        'organiser_id'               => factory(App\Organiser::class)->create()->id,
        'venue_name'                 => $faker->name,
        'venue_name_full'            => $faker->name,
        'location_address'           => $faker->address,
        'location_address_line_1'    => $faker->streetAddress,
        'location_address_line_2'    => $faker->secondaryAddress,
        'location_country'           => $faker->country,
        'location_country_code'      => $faker->countryCode,
        'location_state'             => $faker->state,
        'location_post_code'         => $faker->postcode,
        'location_street_number'     => $faker->buildingNumber,
        'location_lat'               => $faker->latitude,
        'location_long'              => $faker->longitude,
        'location_google_place_id'   => $faker->randomDigit,
        'pre_order_display_message'  => $faker->text,
        'post_order_display_message' => $faker->text,
        'social_share_text'          => 'Check Out [event_title] - [event_url]',
        'social_show_facebook'       => true,
        'social_show_linkedin'       => true,
        'social_show_twitter'        => true,
        'social_show_email'          => true,
        'social_show_googleplus'     => true,
        'location_is_manual'         => 0,
        'is_live'                    => false
    ];
});

$factory->define(App\Order::class, function (Faker\Generator $faker) {
    return [
        'account_id'            => factory(App\Account::class)->create()->id,
        'order_status_id'       => factory(App\OrderStatus::class)->create()->id,
        'first_name'            => $faker->firstName,
        'last_name'             => $faker->lastName,
        'email'                 => $faker->email,
        'ticket_pdf_path'       => '/ticket/pdf/path',
        'order_reference'       => $faker->text,
        'transaction_id'        => $faker->text,
        'discount'              => .20,
        'booking_fee'           => .10,
        'organiser_booking_fee' => .10,
        'order_date'            => Carbon::now(),
        'notes'                 => $faker->text,
        'is_deleted'            => 0,
        'is_cancelled'          => 0,
        'is_partially_refunded' => 0,
        'is_refunded'           => 0,
        'amount'                => 20.00,
        'amount_refunded'       => 0,
        'event_id'              => factory(App\Event::class)->create()->id
    ];
});


$factory->define(App\Ticket::class, function (Faker\Generator $faker) {
    return [
        'user_id'               => factory(App\User::class)->create()->id,
        'edited_by_user_id'     => factory(App\User::class)->create()->id,
        'account_id'            => factory(App\Account::class)->create()->id,
        'order_id'              => factory(App\OrderStatus::class)->create()->id,
        'event_id'              => factory(App\Event::class)->create()->id,
        'title'                 => $faker->name,
        'description'           => $faker->text,
        'price'                 => 50.00,
        'max_per_person'        => 4,
        'min_per_person'        => 1,
        'quantity_available'    => 50,
        'quantity_sold'         => 0,
        'start_sale_date'       => Carbon::now(),
        'end_sale_date'         => Carbon::now()->addDays(20),
        'sales_volume'          => 0,
        'organiser_fees_volume' => 0,
        'is_paused'             => 0
    ];
});

$factory->define(App\OrderItem::class, function (Faker\Generator $faker) {
    return [
        'title'            => $faker->title,
        'quantity'         => 5,
        'unit_price'       => 20.00,
        'unit_booking_fee' => 2.00,
        'order_id'         => factory(App\Order::class)->create()->id
    ];
});

$factory->define(App\EventStats::class, function (Faker\Generator $faker) {
    return [
        'date'                  => Carbon::now(),
        'views'                 => 0,
        'unique_views'          => 0,
        'tickets_sold'          => 0,
        'sales_volumne'         => 0,
        'organiser_fees_volume' => 0,
        'event_id'              => factory(App\Event::class)->create()->id,
    ];
});


$factory->define(App\Attendee::class, function (Faker\Generator $faker) {
    return [
        'order_id'                 => factory(App\Order::class)->create()->id,
        'event_id'                 => factory(App\Event::class)->create()->id,
        'ticket_id'                => factory(App\Ticket::class)->create()->id,
        'first_name'               => $faker->firstName,
        'last_name'                => $faker->lastName,
        'email'                    => $faker->email,
        'reference_index'          => $faker->numberBetween(),
        'private_reference_number' => 1,
        'is_cancelled'             => false,
        'has_arrived'              => false,
        'arrival_time'             => Carbon::now(),
        'account_id'               => factory(App\Account::class)->create()->id,
    ];
});

$factory->define(App\Message::class, function (Faker\Generator $faker) {
    return [
        'message'    => $faker->text,
        'subject'    => $faker->text,
        'recipients' => 0,
    ];
});

$factory->define(App\EventImage::class, function (Faker\Generator $faker) {
    return [
        'image_path' => $faker->imageUrl(),
        'event_id'   => factory(App\Event::class)->create()->id,
        'account_id' => factory(App\Account::class)->create()->id,
        'user_id'    => factory(App\User::class)->create()->id
    ];
});

