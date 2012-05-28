<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(
    'PARAMETERS' => array(
        'PROVIDERS' => array(
            'NAME' => '����������',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'vkontakte,odnoklassniki,mailru,facebook',
            'PARENT' => 'BASE',
        ),
        'HIDDEN' => array(
            'NAME' => '������� ����������',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'other',
            'PARENT' => 'BASE',
        ),
        "TYPE" => Array(
            "PARENT" => "BASE",
            "NAME" => '���',
            "TYPE" => "LIST",
            "VALUES" => array('small' => 'small', 'panel' => 'panel'),
            "DEFAULT" => 'panel',
            "ADDITIONAL_VALUES" => "N",
            "REFRESH" => "Y",
        ),
        "REDIRECT_PAGE" => array(
            'NAME' => '�������� ��������� ����� ������',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N',
            'PARENT' => 'BASE',
        ),
        "UNIQUE_EMAIL" => array(
	  'NAME' => '�������������� ������������� � ����������� email',
	  'TYPE' => 'CHECKBOX',
	  'PARENT' => 'BASE',
	  'DEFAULT' => 'N'
	),
	"SEND_MAIL" => array(
	  'NAME' => '���������� email �������������� ��� ����������� ������������',
	  'TYPE' => 'CHECKBOX',
	  'PARENT' => 'BASE',
	  'DEFAULT' => 'N'
	)
    ),
);
?>
