<?php

class ULoginSync {

	/**
	 * Проверка пользовательских данных, полученных по токену
	 *
	 * @param $u_user - пользовательские данные
	 *
	 * @return bool
	 */
	public static function CheckTokenError($u_user)
	{
		if (!is_array($u_user))
		{
			ShowMessage(array("TYPE" => "ERROR", "MESSAGE" => 'Ошибка работы uLogin: Данные о пользователе содержат неверный формат.'));
			return false;
		}
		if (isset($u_user['error']))
		{
			$strpos = strpos($u_user['error'], 'host is not');
			if ($strpos)
			{
				ShowMessage(array("TYPE" => "ERROR", "MESSAGE" => 'Ошибка работы uLogin: адрес хоста не совпадает с оригиналом'));
				return false;
			}
			switch ($u_user['error'])
			{
				case 'token expired':
					ShowMessage(array("TYPE" => "ERROR", "MESSAGE" => 'Ошибка работы uLogin: время жизни токена истекло'));
					return false;
					break;
				case 'invalid token':
					ShowMessage(array("TYPE" => "ERROR", "MESSAGE" => 'Ошибка работы uLogin: неверный токен'));
					return false;
					break;
				default:
					ShowMessage(array("TYPE" => "ERROR", "MESSAGE" => 'Ошибка работы uLogin:'));
					return false;
			}
		}
		if (!isset($u_user['identity']))
		{
			ShowMessage(array("TYPE" => "ERROR", "MESSAGE" => 'Ошибка работы uLogin: В возвращаемых данных отсутствует переменная "identity"'));
			return false;
		}
		return true;
	}

	/**
	 * Гнерация логина пользователя
	 * в случае успешного выполнения возвращает уникальный логин пользователя
	 *
	 * @param $first_name
	 * @param string $last_name
	 * @param string $nickname
	 * @param string $bdate
	 * @param array $delimiters
	 *
	 * @return string
	 */
	public static function generateNickname($profile)
	{
		$first_name = $profile['first_name'];
		$last_name = isset($profile['last_name']) ? $profile['last_name'] : '';
		$nickname = isset($profile['nickname']) ? $profile['nickname'] : '';
		$bdate = isset($profile['bdate']) ? $profile['bdate'] : '';
		$delimiters = array('.', '_');
		$delim = array_shift($delimiters);
		$first_name = uLogin::uLoginTranslitIt($first_name);
		$first_name_s = substr($first_name, 0, 1);
		$variants = array();
		if (!empty($nickname)) $variants[] = $nickname;
		$variants[] = $first_name;
		if (!empty($last_name))
		{
			$last_name = uLogin::uLoginTranslitIt($last_name);
			$variants[] = $first_name.$delim.$last_name;
			$variants[] = $last_name.$delim.$first_name;
			$variants[] = $first_name_s.$delim.$last_name;
			$variants[] = $first_name_s.$last_name;
			$variants[] = $last_name.$delim.$first_name_s;
			$variants[] = $last_name.$first_name_s;
		}
		if (!empty($bdate))
		{
			$date = explode('.', $bdate);
			$variants[] = $first_name.$date[2];
			$variants[] = $first_name.$delim.$date[2];
			$variants[] = $first_name.$date[0].$date[1];
			$variants[] = $first_name.$delim.$date[0].$date[1];
			$variants[] = $first_name.$delim.$last_name.$date[2];
			$variants[] = $first_name.$delim.$last_name.$delim.$date[2];
			$variants[] = $first_name.$delim.$last_name.$date[0].$date[1];
			$variants[] = $first_name.$delim.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name.$date[2];
			$variants[] = $last_name.$delim.$first_name.$delim.$date[2];
			$variants[] = $last_name.$delim.$first_name.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name.$delim.$date[0].$date[1];
			$variants[] = $first_name_s.$delim.$last_name.$date[2];
			$variants[] = $first_name_s.$delim.$last_name.$delim.$date[2];
			$variants[] = $first_name_s.$delim.$last_name.$date[0].$date[1];
			$variants[] = $first_name_s.$delim.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name_s.$date[2];
			$variants[] = $last_name.$delim.$first_name_s.$delim.$date[2];
			$variants[] = $last_name.$delim.$first_name_s.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name_s.$delim.$date[0].$date[1];
			$variants[] = $first_name_s.$last_name.$date[2];
			$variants[] = $first_name_s.$last_name.$delim.$date[2];
			$variants[] = $first_name_s.$last_name.$date[0].$date[1];
			$variants[] = $first_name_s.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$first_name_s.$date[2];
			$variants[] = $last_name.$first_name_s.$delim.$date[2];
			$variants[] = $last_name.$first_name_s.$date[0].$date[1];
			$variants[] = $last_name.$first_name_s.$delim.$date[0].$date[1];
		}
		$i = 0;
		$exist = true;
		while (true)
		{
			if ($exist = uLogin::uLogin_userExist($variants[$i]) && strlen($variants[$i]) < 4)
			{
				foreach ($delimiters as $del)
				{
					$replaced = str_replace($delim, $del, $variants[$i]);
					if ($replaced !== $variants[$i])
					{
						$variants[$i] = $replaced;
						if (!$exist = uLogin::uLogin_userExist($variants[$i])) break;
					}
				}
			}
			if ($i >= count($variants) - 1 || !$exist) break;
			$i++;
		}
		if ($exist)
		{
			while ($exist)
			{
				$nickname = $first_name.mt_rand(1, 100000);
				$exist = uLogin::uLogin_userExist($nickname);
			}
			return iconv('utf-8', 'windows-1251', $nickname);
		}
		else
		{
			return iconv('utf-8', 'windows-1251', $variants[$i]);
		}
	}

	public static function updateuLoginAccount($id, $new_id, $network)
	{
		$user = new CUser;
		$user->Update($id, array('ADMIN_NOTES' => $network.'='.$new_id));
	}

	/**
	 * Транслит
	 */
	public static function uLoginTranslitIt($str)
	{
		$tr = array("А" => "a", "Б" => "b", "В" => "v", "Г" => "g", "Д" => "d", "Е" => "e", "Ж" => "j", "З" => "z", "И" => "i", "Й" => "y", "К" => "k", "Л" => "l", "М" => "m", "Н" => "n", "О" => "o", "П" => "p", "Р" => "r", "С" => "s", "Т" => "t", "У" => "u", "Ф" => "f", "Х" => "h", "Ц" => "ts", "Ч" => "ch", "Ш" => "sh", "Щ" => "sch", "Ъ" => "", "Ы" => "yi", "Ь" => "", "Э" => "e", "Ю" => "yu", "Я" => "ya", "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j", "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l", "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h", "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y", "ы" => "y", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya");
		if (preg_match('/[^A-Za-z0-9\_\-]/', $str))
		{
			$str = strtr($str, $tr);
			$str = preg_replace('/[^A-Za-z0-9\_\-\.]/', '', $str);
		}
		return $str;
	}

	/**
	 * Проверка существует ли пользователь с заданным логином
	 */
	public function ulogin_userExist($login)
	{
		$loginUsers = CUser::GetList(($by = "id"), ($order = "desc"), array("LOGIN" => $login, "ACTIVE" => "Y")); //$login
		if ($loginUsers->SelectedRowsCount() > 0)
		{
			return false;
		}
		return true;
	}

	/**
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function uloginCheckUserId($user_id)
	{
		global $USER;
		$current_user = $USER->GetID();
		if (($current_user > 0) && ($user_id > 0) && ($current_user != $user_id))
		{
			ShowMessage(array("TYPE" => "ERROR", "MESSAGE" => 'Данный аккаунт привязан к другому пользователю. Вы не можете использовать этот аккаунт'));
			die('<a href="'.$_POST['backurl'].'">Назад</a>');
		}
		return true;
	}

	/**
	 * проверка уникальности email
	 */
	public static function check($arParams)
	{
		if ($arParams['UNIQUE_EMAIL'] == 'Y')
		{
			$emailUsers = CUser::GetList(($by = "id"), ($order = "desc"), array("EMAIL" => $arParams['USER']["EMAIL"], "ACTIVE" => "Y"));
			if (intval($emailUsers->SelectedRowsCount()) > 0)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Обменивает токен на пользовательские данные
	 *
	 * @param bool $token
	 *
	 * @return bool|mixed|string
	 */
	public static function uloginGetUserFromToken($token = false)
	{
		$response = false;
		if ($token)
		{
			$data = array('cms' => 'Bitrix', 'version' => constant('SM_VERSION'));
			$request = 'http://ulogin.ru/token.php?token='.$token.'&host='.$_SERVER['HTTP_HOST'].'&data='.base64_encode(json_encode($data));
			if (in_array('curl', get_loaded_extensions()))
			{
				$c = curl_init($request);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
				$response = curl_exec($c);
				curl_close($c);
			}
			elseif (function_exists('file_get_contents') && ini_get('allow_url_fopen')) $response = file_get_contents($request);
		}
		return $response;
	}

	/**
	 * Регистрация на сайте и в таблице uLogin
	 *
	 * @param Array $u_user - данные о пользователе, полученные от uLogin
	 * @param int $in_db - при значении 1 необходимо переписать данные в таблице uLogin
	 *
	 * @return bool|int|Error
	 */
	public static function RegistrationUser($u_user, $arParams)
	{
		if (!isset($u_user['email']))
		{
			ShowMessage(array("TYPE" => "ERROR", "MESSAGE" => 'Через данную форму выполнить регистрацию невозможно. Сообщите администратору сайта о следующей ошибке:
            Необходимо указать "email" в возвращаемых полях uLogin'));
			die('<br/><a href="'.$_POST['backurl'].'">Назад</a>');
		}
		global $USER;
		global $DB;
		// данные о пользователе отсутствуют в b_users
		$rsUsers = CUser::GetList(($by = "id"), ($order = "desc"), array("EMAIL" => $u_user['email']));
		$arUser = $rsUsers->GetNext();
//		// $check_m_user == true -> есть пользователь с таким email
		$check_m_user = $arUser['ID'] > 0 ? true : false;
		$current_user = $USER->GetID();
		if ($check_m_user)
		{
			if (!isset($u_user["verified_email"]) || intval($u_user["verified_email"]) != 1)
			{
				die('<script src="//ulogin.ru/js/ulogin.js"  type="text/javascript"></script><script type="text/javascript">uLogin.mergeAccounts("'.$_POST['token'].'")</script>'.'Электронный адрес данного аккаунта совпадает с электронным адресом существующего пользователя. Требуется подтверждение на владение указанным email.'.'<br/><a href="'.$_POST['backurl'].'">Назад</a>');
			}
			if (intval($u_user["verified_email"]) == 1)
			{
				$user_id = $current_user;
				$other_u = $DB->Query('SELECT identity,network FROM ulogin_users WHERE userid = "'.$user_id.'"');
				$other = array();
				while ($row = $other_u->Fetch())
				{
					$ident = $row['identity'];
					$key = $row['network'];
					$other[$key] = $ident;
				}
				if ($other)
				{
					if (!isset($u_user['merge_account']))
					{
						die('<script src="//ulogin.ru/js/ulogin.js"  type="text/javascript"></script><script type="text/javascript">uLogin.mergeAccounts("'.$_POST['token'].'","'.$other[$key].'")</script>'.'С данным аккаунтом уже связаны данные из другой социальной сети. Требуется привязка новой учётной записи социальной сети к этому аккаунту'.'<br/><a href="'.$_POST['backurl'].'">Назад</a>');
					}
				}
			}
			$result = $DB->Query('INSERT INTO ulogin_users (id, userid, identity, network) VALUES (NULL,"'.$current_user.'","'.urlencode($u_user['identity']).'","'.$u_user['network'].'")');
			$result = $result->GetNext();
			return $result['id'];
		} else {
			$result = $DB->Query('INSERT INTO ulogin_users (id, userid, identity, network) VALUES (NULL,"'.$current_user.'","'.urlencode($u_user['identity']).'","'.$u_user['network'].'")');
			$result = $result->GetNext();
			return $result['id'];
		}
	}

	/**
	 * Обновление данных о пользователе и вход
	 *
	 * @param $u_user - данные о пользователе, полученные от uLogin
	 * @param $id_customer - идентификатор пользователя
	 *
	 * @return string
	 */
	public function loginUser($u_user, $id_customer)
	{
		global $USER;
		//авторизуем пользователя
		//дописать проверку изменения данных
		$USER->Authorize($id_customer);
	}

	/**
	 * Вывод списка аккаунтов пользователя
	 *
	 * @param int $user_id - ID пользователя (если не задан - текущий пользователь)
	 *
	 * @return string
	 */
	static function getuLoginUserAccountsPanel($user_id = 0)
	{
		global $USER;
		global $DB;
		$current_user = $USER->GetID();
		$user_id = empty($user_id) ? $current_user : $user_id;
		if (empty($user_id)) return '';
		$results = $DB->Query('SELECT * FROM ulogin_users  WHERE  userid= "'.$user_id.'"');
		$networks = array();
		while ($row = $results->Fetch())
		{
			$key = $row['network'];
			$value = $row['identity'];
			$networks[$key] = $value;
		}
		$output = '';
		if ($networks)
		{
			$output .= '<div id="ulogin_accounts">';
			foreach ($networks as $key => $network)
			{
				$output .= "<div data-ulogin-network='{$key}'  data-ulogin-identity='{$network}' class='ulogin_network big_provider {$key}_big'></div>";
			}
			$output .= '</div><div style="clear: both"></div>';
			return $output;
		}
		return '';
	}
}

?>

