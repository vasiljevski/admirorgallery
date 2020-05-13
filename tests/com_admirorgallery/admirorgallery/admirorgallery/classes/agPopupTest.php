<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class agPopupTest extends TestCase {

    public function testInstance() {
        $popup = new agPopup();
        $this->assertInstanceOf(
                agPopup::class,
                $popup);
    }

}
