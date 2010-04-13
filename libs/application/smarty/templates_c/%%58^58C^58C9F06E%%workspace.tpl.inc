<?php /* Smarty version 2.6.19, created on 2008-06-03 14:25:11
         compiled from Home/workspace.tpl */

$this->_cache_serials['/p1/hosting/tweeem/libs/application/smarty/templates_c//%%58^58C^58C9F06E%%workspace.tpl.inc'] = 'c35efb7ef8e4d047e9898cf9df558902';

?><?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'Home/workspace.tpl', 31, false),array('block', 'dynamic', 'Home/workspace.tpl', 32, false),)), $this); ?>
<?php
function _smarty_tplfunc_c35efb7ef8e4d047e9898cf9df558902_0(&$_smarty)
{
$_smarty->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], null, $_smarty, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php echo ((is_array($_tmp=time())) ? $_smarty->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d %H:%M:%S") : smarty_modifier_date_format($_tmp, "%Y-%m-%d %H:%M:%S")); ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], $_block_content, $_smarty, $_block_repeat); }  array_pop($_smarty->_tag_stack); 
}



?>
