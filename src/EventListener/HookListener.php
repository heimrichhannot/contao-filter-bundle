<?php


namespace HeimrichHannot\FilterBundle\EventListener;


use Contao\System;
use HeimrichHannot\Blocks\BlockModuleModel;

class HookListener
{
    /**
     * exclude/include BlockModule by filter parameter
     *
     * @param BlockModuleModel $block
     *
     * @return bool
     */
    public function isBlockVisible(BlockModuleModel $block): bool
    {
        if($block->useFilter) {
            $sessionKey  = System::getContainer()->get('huh.filter.manager')->findById($block->filter)->getSessionKey();
            $sessionData = System::getContainer()->get('huh.filter.session')->getData($sessionKey);
            
            $filterKeywords = preg_split('/\s*,\s*/', trim($block->filterKeywords), -1, PREG_SPLIT_NO_EMPTY);
            foreach($filterKeywords as $keyword) {
                $keyword = html_entity_decode($keyword);
                $equals     = false === strpos($keyword,'!=') ? true : false;
                $delimeter  = $equals ? '=' : '!=';
                $params     = explode($delimeter,$keyword);
                
                if(isset($sessionData[$params[0]]) && ((!$equals && $sessionData[$params[0]] == $params[1]) || ($equals && $sessionData[$params[0]] != $params[1]))) {
                    return false;
                }
            }
        }
        
        return true;
    }
}
