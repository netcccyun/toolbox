<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

class Cron extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('cron')
            ->setDescription('定时数据清理任务');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        Db::name('querycache')->where('uptime','<',date('Y-m-d H:i:s', strtotime('-30 days')))->delete();
        $output->writeln('定时数据清理任务执行完毕');
    }
}
