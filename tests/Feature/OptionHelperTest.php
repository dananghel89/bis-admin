<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

/**
 * No RefreshDatabase here: each test starts with an empty in-memory database,
 * so the first test can check get_option() without an options table.
 */
class OptionHelperTest extends TestCase
{
    public function test_works_without_an_options_table()
    {
        $this->assertSame('', get_option('site_title'));
    }

    public function test_returns_database_values_and_empty_for_unknown_names()
    {
        $this->artisan('migrate');
        app()->forgetInstance('project.options');

        $this->assertSame('BIS', get_option('site_title'));
        $this->assertSame('1', get_option('membership'));
        $this->assertSame('', get_option('no_such_option'));
    }

    public function test_reads_new_values_after_the_cache_is_reset()
    {
        $this->artisan('migrate');
        $this->assertSame('BIS', get_option('site_title'));

        DB::table('options')->where('name', 'site_title')->update(['value' => 'Changed']);
        $this->assertSame('BIS', get_option('site_title'), 'cached for the request');

        app()->forgetInstance('project.options');
        $this->assertSame('Changed', get_option('site_title'));
    }
}
