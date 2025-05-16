
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();
header('Content-Type: text/html; charset=UTF-8');

$db_host = 'localhost';
$db_name = 'u68529';
$db_user = 'u68529';
$db_pass = '4465490';


$errors = [];
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


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
   
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
    
    include('form.php');
    exit();
}


$values = $_POST;
$values['languages'] = $_POST['languages'] ?? [];
$values['contract_agreed'] = isset($_POST['contract_agreed']);


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

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

 
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
    
  
    $stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
    foreach ($values['languages'] as $lang_id) {
        $stmt->execute([$app_id, $lang_id]);
    }
    
   
    $login = 'user_' . substr(md5(time()), 0, 8);
    $password = substr(md5(uniqid()), 0, 8);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (login, password, application_id) VALUES (?, ?, ?)");
    $stmt->execute([$login, $password_hash, $app_id]);
    
    $_SESSION['generated_credentials'] = [
        'login' => $login,
        'password' => $password
    ];
    
   
    foreach ($values as $key => $value) {
        setcookie($key.'_value', '', time() - 3600, '/');
    }
    
    header("Location: index.php");
    exit();
    
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
?>
