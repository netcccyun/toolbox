<?php
namespace app\lib;
use Exception;
/**
 * class Ip2Region
 * 为兼容老版本调度而创建
 * @author Anyon<zoujingli@qq.com>
 * @datetime 2022/07/18
 */
class Ip2Region
{
    /**
     * 查询实例对象
     * @var XdbSearcher
     */
    private $searcher;

    /**
     * 初始化构造方法
     * @throws Exception
     */
    public function __construct()
    {
        $this->searcher = XdbSearcher::newWithFileOnly(dirname(__FILE__) . '/ip2region.xdb');
    }

    /**
     * 兼容原 memorySearch 查询
     * @param string $ip
     * @return string
     * @throws Exception
     */
    public function search($ip)
    {
        return $this->searcher->search($ip);
    }

    /**
     * destruct method
     * resource destroy
     */
    public function __destruct()
    {
        $this->searcher->close();
        unset($this->searcher);
    }
}