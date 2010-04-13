<?php /* Smarty version 2.6.19, created on 2008-06-10 14:46:38
         compiled from Ajax/list.tpl */

$this->_cache_serials['/p1/hosting/tweeem/libs/application/smarty/templates_c//%%E4^E42^E4268285%%list.tpl.inc'] = 'becb783ef1d83601810de668fda57340';

?><?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'Ajax/list.tpl', 22, false),array('block', 'dynamic', 'Ajax/list.tpl', 23, false),)), $this); ?>
<?php
function _smarty_tplfunc_becb783ef1d83601810de668fda57340_0(&$_smarty)
{
$_smarty->_tag_stack[] = array('dynamic', array()); $_block_repeat=true;smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], null, $_smarty, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo ''; ?><?php echo ((is_array($_tmp=time())) ? $_smarty->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d %H:%M:%S") : smarty_modifier_date_format($_tmp, "%Y-%m-%d %H:%M:%S")); ?><?php echo ''; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_dynamic($_smarty->_tag_stack[count($_smarty->_tag_stack)-1][1], $_block_content, $_smarty, $_block_repeat); }  array_pop($_smarty->_tag_stack); 
}



?>
