<?php
/**
 * Конфиг для списка прав доступа
 */

/**
 * Создание констант
 * Для быстродействия предпочтительнее использовать константы, нежели строковые значения
 */

define('ACL_ROLE_GUEST',	0x00);
define('ACL_ROLE_USER',		0x01);
define('ACL_ROLE_ADMIN',	0xff);

define('ACL_RESOURCE_WORKSPACE',	0x01);

define('ACL_ACTION_WORK',	0x01);

/**
 * Массив существующих ролей
 */
$data['roles'] = array(
	ACL_ROLE_GUEST,
	ACL_ROLE_USER,
	ACL_ROLE_ADMIN,
);

/**
 * Роль с абсолютными правами доступа - root, admin
 */
$data['complete_access_role'] = ACL_ROLE_ADMIN;

/**
 * Роль с правами доступа по умолчанию - guest
 */
$data['default_access_role'] = ACL_ROLE_GUEST;

/**
 * Иерархия ролей, кто от кого наследует права доступа
 * Допустимо множественное наследование
 * !!! Избегайте наличия циклов в иерархии
 */
$data['hierarchy'] = array(
	ACL_ROLE_USER => array(ACL_ROLE_GUEST),
);

/**
 * Ресурсы с перечислением возможных производимых над ними действий
 */
$data['resources'] = array(
	ACL_RESOURCE_WORKSPACE => array(
		ACL_ACTION_WORK,
	),
);

/**
 * Список разрешения доступа
 * array(
 * 		кто => array(
 * 			над чем => array(какие действия может делать, ...),
 * 		),
 * )
 */
$data['allow'] = array(
	ACL_ROLE_GUEST => array(

	),

	ACL_ROLE_USER => array(
		ACL_RESOURCE_WORKSPACE => array(ACL_ACTION_WORK),
	),
);

/**
 * Список запрещения доступа
 * array(
 * 		кто => array(
 * 			над чем => array(какие действия НЕ может делать, ...),
 * 		),
 * )
 */
$data['deny'] = array(

);

return $data;