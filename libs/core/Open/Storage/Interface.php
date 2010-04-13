<?php
/**
 * Интерфейс хранилища данных
 * @package OpenStruct
 */

/**
 * Интерфейс хранилища данных
 */
interface Open_Storage_Interface
{
	/**
	 * Получить значение переменной $name
	 *
	 * @param string $name Имя переменной
	 * @return mixed
	 */
	public function get($name);

	/**
	 * Установить переменную $name со значением $value
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value);

	/**
	 * Проверить существование переменной $name
	 *
	 * @param string $name
	 * @return bool
	 */
	public function exists($name);

	/**
	 * Удалить переменную $name
	 *
	 * @param string $name
	 * @return object $this
	 */
	public function delete($name);
}