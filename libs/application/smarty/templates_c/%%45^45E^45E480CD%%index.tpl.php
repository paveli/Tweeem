<?php /* Smarty version 2.6.19, created on 2008-05-12 10:05:41
         compiled from index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', 'index.tpl', 34, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head><title>'; ?><?php echo $this->_tpl_vars['view']->getTitle(); ?><?php echo '</title>'; ?><?php echo '<meta http-equiv="Content-Type" content="text/html; charset='; ?><?php echo $this->_tpl_vars['config']->get('charset'); ?><?php echo '"/>'; ?><?php echo '<meta http-equiv="Content-Script-Type" content="text/javascript" />'; ?><?php echo ''; ?><?php $_from = $this->_tpl_vars['view']->getCss(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['item']):
?><?php echo '<link rel="stylesheet" type="text/css" href="/'; ?><?php echo @CSS_DIR; ?><?php echo ''; ?><?php echo $this->_tpl_vars['item']; ?><?php echo ''; ?><?php echo @CSSEXT; ?><?php echo '" />'; ?><?php endforeach; endif; unset($_from); ?><?php echo ''; ?><?php echo ''; ?><?php $_from = $this->_tpl_vars['view']->getJs(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['item']):
?><?php echo '<script language="JavaScript" type="text/javascript" src="/'; ?><?php echo @JS_DIR; ?><?php echo ''; ?><?php echo $this->_tpl_vars['item']; ?><?php echo ''; ?><?php echo @JSEXT; ?><?php echo '"></script>'; ?><?php endforeach; endif; unset($_from); ?><?php echo '</head><body>'; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=$this->_tpl_vars['view']->getBody())) ? $this->_run_mod_handler('cat', true, $_tmp, @TPLEXT) : smarty_modifier_cat($_tmp, @TPLEXT)), 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo '</body></html>'; ?>