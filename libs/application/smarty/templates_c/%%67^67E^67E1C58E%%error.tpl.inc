<?php /* Smarty version 2.6.19, created on 2008-05-26 17:57:21
         compiled from error.tpl */

$this->_cache_serials['/p1/hosting/tweeem/libs/application/smarty/templates_c//%%67^67E^67E1C58E%%error.tpl.inc'] = '683ced58224f131adf18ddd3f85a90cb';

?><?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'dynamic', 'error.tpl', 6, false),)), $this); ?>
<?php
function _smarty_tplfunc_683ced58224f131adf18ddd3f85a90cb_0(&$_smarty)
{
$_smarty->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], null, $_smarty, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php if (isset ( $_smarty->_tpl_vars['message'] )): ?><?php echo '<div id="error">'; ?><?php echo $_smarty->_tpl_vars['message']; ?><?php echo '</div>'; ?><?php endif; ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], $_block_content, $_smarty, $_block_repeat); }  array_pop($_smarty->_tag_stack); 
}



?>
