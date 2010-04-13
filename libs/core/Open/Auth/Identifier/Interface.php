<?php
/**
 * Интерфейс идентификатора аутентификации
 * @package OpenStruct
 */

/**
 * Интерфейс идентификатора аутентификации
 */
interface Open_Auth_Identifier_Interface
{
	/**
	 * Получить сущность прошедшую идентификацию
	 *
	 * @param mixed $identity
	 */
	public function identity($identity=NULL);

	/**
	 * Идентифицировать по значению уникальности и значению удостоверения личности
	 * Метод должен возвращать константу успеха операции. Успех должен быть истинным значением (e.g. 1, '1', true)
	 *
	 * @param string $identity
	 * @param string $credential
	 * @return mixed
	 */
	public function identify($identity, $credential);
}