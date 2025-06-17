<?php

namespace app\workman\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\console\input\Option;
use Workerman\Worker;

class Notice extends  Command
{
    /**
     * 命令Input配置
     */
    protected function configure()
    {
        $this->setName('notice')
            ->addArgument('action', Argument::OPTIONAL, "action")
            ->addOption('other', '-d', Option::VALUE_OPTIONAL, 'test')
            ->setDescription("长链接已经启动");
    }
    /**
     * 重置Cli参数
     */
    protected function resetCli()
    {
        global $argv, $argc;

        $file = "{$argv['0']} {$argv['1']}";
        $action = $argv['2'];
        $extend = empty($argv['3']) ? '' : $argv['3'];
        $argv = [];
        $argv[] = $file;
        $argv[] = $action;
        if ($extend)
        {
            $argv[] = $extend;
        }
        $argc = count($argv);

        $_SERVER['argv'] = $argv;
        $_SERVER['argc'] = $argc;
    }

    /**
     * 命令响应
     * @param Input $input
     * @param Output $output
     * @return int|void|null
     */
    protected function execute(Input $input, Output $output)
    {
        //01.重置Cli命令行参数
        $this->resetCli();
        new \app\workman\controller\Notice();
    }
}