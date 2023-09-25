<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;
use App\Helpers\TeHelper;
use Carbon\Carbon;

class Te_Helper_Test extends TestCase
{

    public function test_will_expire_at()
    {
        $dueTime = '2023-09-25 17:00:00';
        $createdAt = '2023-09-23 12:00:00';

        $expected = '2023-09-25 15:30:00';

        $actual = TeHelper::willExpireAt($dueTime, $createdAt);

        $this->assertEquals($expected, $actual);
    }

    public function test_will_expire_at_within_90_minutes()
    {
        $dueTime = '2023-09-25 17:00:00';
        $createdAt = '2023-09-25 16:00:00';

        $expected = '2023-09-25 17:00:00';

        $actual = TeHelper::willExpireAt($dueTime, $createdAt);

        $this->assertEquals($expected, $actual);
    }

    public function test_will_expire_at_within_24_hours()
    {
        $dueTime = '2023-09-25 17:00:00';
        $createdAt = '2023-09-24 18:00:00';

        $expected = '2023-09-25 15:30:00';

        $actual = TeHelper::willExpireAt($dueTime, $createdAt);

        $this->assertEquals($expected, $actual);
    }

    public function test_will_expire_after_24_hours()
    {
        $dueTime = '2023-09-25 17:00:00';
        $createdAt = '2023-09-23 12:00:00';

        $expected = '2023-09-25 04:00:00';

        $actual = TeHelper::willExpireAt($dueTime, $createdAt);

        $this->assertEquals($expected, $actual);
    }

}
