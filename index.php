<?php

function getData( $get ) {

// Обработка входящих данных

	$get_array = [];
	$res_array = [];
	foreach ( $get['ident'] as $key => $value ) {
		$get_array[ $value ] = [
			'value'   => $get['value'][ $key ],
			'version' => $get['version'][ $key ]
		];
	}

// Соединение с БД

	$dsn      = 'mysql:dbname=uniweb; host=localhost';
	$user     = 'root';
	$password = 'root';

	try {
		$db = new PDO( $dsn, $user, $password );
	} catch ( PDOException $e ) {
		echo 'Connection failed: ' . $e->getMessage();
	}

// Выбираем все данные из БД и записываем в массив

	$stmt   = $db->query( 'SELECT * FROM `data` ' );
	$res_db = [];

	while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
		$res_db[ $row['ident'] ] = [
			'value'   => $row['value'],
			'version' => $row['version']
		];
	}

// Сравниваем данные запроса с данными БД

// список идентификаторов, которые пришли в запросе и отсутствуют в БД

	$res_array['delete'] = array_keys( array_diff_key( $get_array, $res_db ) );

// список значений и версий по идентификаторам, где версия в БД стала больше чем версия пришедшая в запросе

	foreach ( $get_array as $key => $value ) {
		if ( array_key_exists( $key, $res_db ) ) {
			if ( intval( $res_db[ $key ]['version'] ) > intval( $get_array[ $key ]['version'] ) ) {
				$res_array['update'][ $key ] = [
					'value'   => $res_db[ $key ]['value'],
					'version' => $res_db[ $key ]['version']
				];
			}
		}
	}

// список значений и версий по идентификаторам, которые отсутствуют в пришедшем запросе, но есть в БД

	$res_array['new'] = array_diff_key( $res_db, $get_array );

	return serialize( $res_array );

}























