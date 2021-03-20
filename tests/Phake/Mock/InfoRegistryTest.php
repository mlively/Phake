<?php declare(strict_types=1);

namespace Phake\Mock;
/**
 * Created by PhpStorm.
 * User: mlively
 * Date: 3/30/14
 * Time: 3:29 PM
 */

use Phake;
use PHPUnit\Framework\TestCase;

class InfoRegistryTest extends TestCase
{
    /**
     * @var InfoRegistry
     */
    private $registry;

    /**
     * @Mock
     * @var Phake\Mock\Info
     */
    private $info1;

    /**
     * @Mock
     * @var Phake\Mock\Info
     */
    private $info2;

    /**
     * @Mock
     * @var Phake\Mock\Info
     */
    private $info3;

    public function setUp(): void
    {
        Phake::initAnnotations($this);
        $this->registry = new Phake\Mock\InfoRegistry();
        $this->registry->addInfo($this->info1);
        $this->registry->addInfo($this->info2);
        $this->registry->addInfo($this->info3);
    }

    public function testReset()
    {
        $this->registry->resetAll();

        Phake::verify($this->info1)->resetInfo();
        Phake::verify($this->info2)->resetInfo();
        Phake::verify($this->info3)->resetInfo();
    }
}
