<?php

/*
 * This file is part of VirtPHP.
 *
 * (c) Jordan Kasper <github @jakerella>
 *     Ben Ramsey <github @ramsey>
 *     Jacques Woodcock <github @jwoodcock>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Virtphp\Test\Command;

use Virtphp\Command\CloneCommand;
use Virtphp\TestCase;
use Virtphp\TestOutput;

class CloneCommandTest extends TestCase
{
    /**
     * @covers Virtphp\Command\CloneCommand::configure
     */
    public function testConfigure()
    {
        $command = new CloneCommand();

        $this->assertEquals('clone', $command->getName());
        $this->assertEquals('Create new virtphp from existing path.', $command->getDescription());
        $this->assertTrue($command->getDefinition()->hasArgument('new-env-name'));
        $this->assertTrue($command->getDefinition()->hasArgument('existing-env-path'));
    }

    /**
     * @covers Virtphp\Command\CloneCommand::execute
     */
    public function testExecute()
    {
        $cloner = $this->getMockBuilder('Virtphp\Workers\Cloner', array('execute'))
            ->disableOriginalConstructor()
            ->getMock();
        $cloner->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(true));

        $command = $this->getMock('Virtphp\Command\CloneCommand', array('isValidPath', 'getWorker'));
        $command->expects($this->any())
            ->method('isValidPath')
            ->will($this->returnValue(true));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnValue($cloner));

        $execute = new \ReflectionMethod('Virtphp\Command\CloneCommand', 'execute');
        $execute->setAccessible(true);

        $input = new \Symfony\Component\Console\Input\ArgvInput(
            array('file.php', 'foobar', '/foo/bar/baz'),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertTrue($result);
        $this->assertCount(2, $output->messages);
        $this->assertEquals(
            'Your new cloned virtual php environment has been created.',
            $output->messages[0]
        );
        $this->assertStringMatchesFormat(
            'Cloned from:%s',
            $output->messages[1]
        );
    }

    /**
     * @covers Virtphp\Command\CloneCommand::execute
     */
    public function testExecuteWithInvalidName()
    {
        $cloner = $this->getMockBuilder('Virtphp\Workers\Cloner', array('execute'))
            ->disableOriginalConstructor()
            ->getMock();
        $cloner->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(true));

        $command = $this->getMock('Virtphp\Command\CloneCommand', array('isValidPath', 'getWorker'));
        $command->expects($this->any())
            ->method('isValidPath')
            ->will($this->returnValue(true));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnValue($cloner));

        $execute = new \ReflectionMethod('Virtphp\Command\CloneCommand', 'execute');
        $execute->setAccessible(true);

        $input = new \Symfony\Component\Console\Input\ArgvInput(
            array('file.php', '1foobar', '/foo/bar/baz'),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
        $this->assertCount(1, $output->messages);
        $this->assertEquals(
            'Sorry, but that is not a valid environment name.',
            $output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Command\CloneCommand::execute
     */
    public function testExecuteWithInvalidPath()
    {
        $cloner = $this->getMockBuilder('Virtphp\Workers\Cloner', array('execute'))
            ->disableOriginalConstructor()
            ->getMock();
        $cloner->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(true));

        $command = $this->getMock('Virtphp\Command\CloneCommand', array('isValidPath', 'getWorker'));
        $command->expects($this->any())
            ->method('isValidPath')
            ->will($this->returnValue(false));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnValue($cloner));

        $execute = new \ReflectionMethod('Virtphp\Command\CloneCommand', 'execute');
        $execute->setAccessible(true);

        $input = new \Symfony\Component\Console\Input\ArgvInput(
            array('file.php', 'foobar', '/foo/bar/baz'),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
        $this->assertCount(0, $output->messages);
    }

    /**
     * @covers Virtphp\Command\CloneCommand::execute
     */
    public function testExecuteWithFailedExecution()
    {
        $cloner = $this->getMockBuilder('Virtphp\Workers\Cloner', array('execute'))
            ->disableOriginalConstructor()
            ->getMock();
        $cloner->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(false));

        $command = $this->getMock('Virtphp\Command\CloneCommand', array('isValidPath', 'getWorker'));
        $command->expects($this->any())
            ->method('isValidPath')
            ->will($this->returnValue(true));
        $command->expects($this->any())
            ->method('getWorker')
            ->will($this->returnValue($cloner));

        $execute = new \ReflectionMethod('Virtphp\Command\CloneCommand', 'execute');
        $execute->setAccessible(true);

        $input = new \Symfony\Component\Console\Input\ArgvInput(
            array('file.php', 'foobar', '/foo/bar/baz'),
            $command->getDefinition()
        );

        $output = new TestOutput();

        $result = $execute->invoke($command, $input, $output);

        $this->assertFalse($result);
        $this->assertCount(0, $output->messages);
    }

    /**
     * @covers Virtphp\Command\CloneCommand::isValidPath
     */
    public function testIsValidPath()
    {
        $filesystem = $this->getMock('Symfony\Component\Filesystem\Filesystem', array('exists'));
        $filesystem->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $command = $this->getMock('Virtphp\Command\CloneCommand', array('getFilesystem'));
        $command->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystem));

        $isValidPath = new \ReflectionMethod('Virtphp\Command\CloneCommand', 'isValidPath');
        $isValidPath->setAccessible(true);

        $output = new TestOutput();

        $result = $isValidPath->invoke($command, '/example/foo/bar', $output);

        $this->assertTrue($result);
        $this->assertCount(0, $output->messages);
    }

    /**
     * @covers Virtphp\Command\CloneCommand::isValidPath
     */
    public function testIsValidPathNotExists()
    {
        $filesystem = $this->getMock('Symfony\Component\Filesystem\Filesystem', array('exists'));
        $filesystem->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));

        $command = $this->getMock('Virtphp\Command\CloneCommand', array('getFilesystem'));
        $command->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystem));

        $isValidPath = new \ReflectionMethod('Virtphp\Command\CloneCommand', 'isValidPath');
        $isValidPath->setAccessible(true);

        $output = new TestOutput();

        $result = $isValidPath->invoke($command, '/example/foo/bar', $output);

        $this->assertFalse($result);
        $this->assertCount(1, $output->messages);
        $this->assertEquals(
            'Sorry, but there is no VirtPHP environment at that location.',
            $output->messages[0]
        );
    }

    /**
     * @covers Virtphp\Command\CloneCommand::isValidPath
     */
    public function testIsValidPathNotValidVirtPhp()
    {
        $filesystem = $this->getMock('Symfony\Component\Filesystem\Filesystem', array('exists'));
        $filesystem->expects($this->any())
            ->method('exists')
            ->will($this->onConsecutiveCalls(true, false));

        $command = $this->getMock('Virtphp\Command\CloneCommand', array('getFilesystem'));
        $command->expects($this->any())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystem));

        $isValidPath = new \ReflectionMethod('Virtphp\Command\CloneCommand', 'isValidPath');
        $isValidPath->setAccessible(true);

        $output = new TestOutput();

        $result = $isValidPath->invoke($command, '/example/foo/bar', $output);

        $this->assertFalse($result);
        $this->assertCount(1, $output->messages);
        $this->assertEquals(
            'This directory does not contain a valid VirtPHP environment!',
            $output->messages[0]
        );
    }
}
