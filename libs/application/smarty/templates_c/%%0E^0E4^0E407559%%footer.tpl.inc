<?php /* Smarty version 2.6.19, created on 2008-05-15 10:50:15
         compiled from footer.tpl */

$this->_cache_serials['/p1/hosting/tweeem/libs/application/smarty/templates_c//%%0E^0E4^0E407559%%footer.tpl.inc'] = '06a29a6011a8c7a011763e35ab6ca4ec';

?><?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'dynamic', 'footer.tpl', 11, false),)), $this); ?>
<?php
function _smarty_tplfunc_06a29a6011a8c7a011763e35ab6ca4ec_0(&$_smarty)
{
$_smarty->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], null, $_smarty, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php if ($_smarty->_tpl_vars['user']['role'] != @ACL_ROLES_GUEST): ?><?php echo '<a href="/logout/"><button>Logout</button></a>'; ?><?php endif; ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], $_block_content, $_smarty, $_block_repeat); }  array_pop($_smarty->_tag_stack); 
}



?>
