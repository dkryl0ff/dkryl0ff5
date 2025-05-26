<?php
// Инициализация сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();
header('Content-Type: text/html; charset=UTF-8');

// Настройки базы данных
$db_host = 'localhost';
$db_name = 'u68529';
$db_user = 'u68529';
$db_pass = '4465490';

// Инициализация переменных
$errors = $_SESSION['errors'] ?? [];
$generated_credentials = $_SESSION['generated_credentials'] ?? null;
$login = $_SESSION['login'] ?? null;

// Основной массив значений для формы
$values = [
    'full_name' => '',
    'phone' => '',
    'email' => '',
    'birth_date' => '',
    'gender' => '',
    'biography' => '',
    'contract_agreed' => false,
    'languages' => []
];

// Загрузка данных для авторизованного пользователя
if (!empty($login)) {
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Получаем ID заявки пользователя
        $stmt = $pdo->prepare("SELECT application_id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        
        if ($user && !empty($user['application_id'])) {
            // Получаем данные заявки
            $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
            $stmt->execute([$user['application_id']]);
            $application = $stmt->fetch();
            
            if ($application) {
                // Получаем языки программирования
                $stmt = $pdo->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
                $stmt->execute([$user['application_id']]);
                $languages = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Заполняем массив значений
                $values = [
                    'full_name' => $application['full_name'] ?? '',
                    'phone' => $application['phone'] ?? '',
                    'email' => $application['email'] ?? '',
                    'birth_date' => $application['birth_date'] ?? '',
                    'gender' => $application['gender'] ?? '',
                    'biography' => $application['biography'] ?? '',
                    'contract_agreed' => !empty($application['contract_agreed']),
                    'languages' => $languages ?: []
                ];
            }
        }
    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
} 
// Для неавторизованных - загрузка из куки
else {
    foreach ($values as $key => &$value) {
        if (isset($_COOKIE[$key.'_value'])) {
            $value = $key === 'contract_agreed' 
                ? (bool)$_COOKIE[$key.'_value']
                : $_COOKIE[$key.'_value'];
        }
    }
    
    if (isset($_COOKIE['languages_value'])) {
        $values['languages'] = explode(',', $_COOKIE['languages_value']);
    }
}

// Обработка POST-запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $values = $_POST;
    $values['languages'] = $_POST['languages'] ?? [];
    $values['contract_agreed'] = isset($_POST['contract_agreed']);

    // Валидация данных
    $validation_failed = false;

    if (empty($values['full_name']) || !preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]{2,150}$/u', $values['full_name'])) {
        $errors['full_name'] = true;
        $validation_failed = true;
    }

    if (empty($values['phone']) || !preg_match('/^\+?\d{10,15}$/', $values['phone'])) {
        $errors['phone'] = true;
        $validation_failed = true;
    }

    if (empty($values['email']) || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = true;
        $validation_failed = true;
    }

    $today = new DateTime();
    $birthdate = DateTime::createFromFormat('Y-m-d', $values['birth_date']);
    if (empty($values['birth_date']) || !$birthdate || $birthdate > $today) {
        $errors['birth_date'] = true;
        $validation_failed = true;
    }

    if (empty($values['gender']) || !in_array($values['gender'], ['male', 'female', 'other'])) {
        $errors['gender'] = true;
        $validation_failed = true;
    }

    if (empty($values['languages'])) {
        $errors['languages'] = true;
        $validation_failed = true;
    }

    if (!$values['contract_agreed']) {
        $errors['contract_agreed'] = true;
        $validation_failed = true;
    }

    if ($validation_failed) {
        foreach ($values as $key => $value) {
            setcookie($key.'_value', is_array($value) ? implode(',', $value) : $value, time() + 3600, '/');
        }
        
        $_SESSION['errors'] = $errors;
        header('Location: index.php');
        exit();
    }

    // Сохранение в базу данных
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Вставка основной информации
        $stmt = $pdo->prepare("INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, contract_agreed) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $values['full_name'],
            $values['phone'],
            $values['email'],
            $values['birth_date'],
            $values['gender'],
            $values['biography'],
            $values['contract_agreed'] ? 1 : 0
        ]);
        
        $app_id = $pdo->lastInsertId();
        
        // Вставка языков программирования
        $stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        foreach ($values['languages'] as $lang_id) {
            $stmt->execute([$app_id, $lang_id]);
        }
        
        // Создание пользователя (только если это новая регистрация)
        if (empty($login)) {
            $login = 'user_' . substr(md5(time()), 0, 8);
            $password = substr(md5(uniqid()), 0, 8);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (login, password, application_id) VALUES (?, ?, ?)");
            $stmt->execute([$login, $password_hash, $app_id]);
            
            $_SESSION['generated_credentials'] = [
                'login' => $login,
                'password' => $password
            ];
        }
        
        // Очистка куков
        foreach ($values as $key => $value) {
            setcookie($key.'_value', '', time() - 3600, '/');
        }
        
        header("Location: index.php");
        exit();
        
    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
}

// Подключение формы
include('form.php');

// Очистка временных данных сессии
unset($_SESSION['errors'], $_SESSION['generated_credentials']);
?>