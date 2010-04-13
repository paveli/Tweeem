<?php /* Smarty version 2.6.19, created on 2008-06-03 11:39:08
         compiled from header.tpl */

$this->_cache_serials['/p1/hosting/tweeem/libs/application/smarty/templates_c//%%F7^F7F^F7F34188%%header.tpl.inc'] = 'b7cea796fa04cbd4b5999c734bf0f94f';

?><?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'dynamic', 'header.tpl', 17, false),)), $this); ?>
<?php
function _smarty_tplfunc_b7cea796fa04cbd4b5999c734bf0f94f_0(&$_smarty)
{
$_smarty->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], null, $_smarty, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php if ($_smarty->_tpl_vars['acl']->isAllowed($_smarty->_tpl_vars['user']['role'],@ACL_RESOURCE_WORKSPACE,@ACL_ACTION_WORK)): ?><?php echo '<a id="logout" href="/logout/">Logout</a>'; ?><?php endif; ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], $_block_content, $_smarty, $_block_repeat); }  array_pop($_smarty->_tag_stack); 
}



?>
