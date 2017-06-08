<?php
 
namespace Art\Contact\Plugin;
 
class PostPlugin
{        
    public function aroundExecute(\Magento\Contact\Controller\Index\Post $subject, \Closure $proceed)
    {
        // logging to test override    
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get('\Psr\Log\LoggerInterface');
        $logger->debug(__METHOD__ . ' -111- ART' . __LINE__);
        
        // call the core observed function
        $returnValue = $proceed(); 
        
        // logging to test override        
        $logger->debug(__METHOD__ . ' -222- ART' . __LINE__);
        
        return $returnValue;
    }
}
?>