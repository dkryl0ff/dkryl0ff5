
<?php

header('Content-Type: text/html; charset=UTF-8');

$values = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['errors'] ?? [];
$generated_credentials = $_SESSION['generated_credentials'] ?? null;
$login = $_SESSION['login'] ?? null;


try {
    $db_host = 'localhost';
    $db_name = 'u68529';
    $db_user = 'u68529';
    $db_pass = '4465490';
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $stmt = $pdo->query("SELECT * FROM programming_languages");
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $languages = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма</title>
    <style>
       :root {
  --primary: #7c3aed;
  --primary-hover: #571CB7FF;
  --secondary: #f59e0b;
  --error: #dc2626;
  --success: #10b981;
  --text: #1e293b;
  --text-light: #64748b;
  --bg: #f8fafc;
  --border: #e2e8f0;
  --radius-lg: 16px;
  --radius-md: 12px;
  --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  --shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

body {
  font-family: 'Inter', system-ui, sans-serif;
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
  color: var(--text);
  background: linear-gradient(135deg, #2EC95AFF 0%, #2EC95AFF 100%);
  line-height: 1.6;
}

.form-group {
  margin-bottom: 1.5rem;
  position: relative;
}

label {
  display: block;
  margin-bottom: 0.75rem;
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text);
  letter-spacing: -0.01em;
}

.input-label {
  display: block;
  margin-bottom: 0.75rem;
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text);
}

.input-group {
  margin-top: 0.5rem;
}

.input-option {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: var(--radius-md);
  transition: all 0.2s ease;
  cursor: pointer;
}

.input-option:hover {
  background-color: rgba(124, 58, 237, 0.05);
}

.input-option input[type="radio"],
.input-option input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: var(--primary);
  margin: 0;
  transform: scale(1);
}

.option-label {
  font-weight: 500;
  cursor: pointer;
  user-select: none;
  margin-bottom: 0;
}

input,
select,
textarea {
  width: 100%;
  padding: 1rem 1.5rem;
  border: 2px solid var(--border);
  border-radius: var(--radius-md);
  background-color: white;
  font-size: 1rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: var(--shadow);
}

input:focus,
select:focus,
textarea:focus {
  border-color: var(--primary);
  outline: none;
  box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.2);
  transform: translateY(-2px);
}

select[multiple] {
  min-height: 120px;
  padding: 1rem;
  background-image: none;
}

.error {
  border-color: var(--error) !important;
  animation: pulse 0.5s;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.02); }
}

.error-message {
  color: var(--error);
  font-size: 0.85rem;
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background-color: rgba(220, 38, 38, 0.05);
  border-radius: var(--radius-md);
}

.error-message::before {
  content: "❗";
}

.credentials {
  background: rgba(255, 255, 255, 0.9);
  padding: 1.5rem;
  margin-bottom: 2rem;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow);
  border-left: 4px solid var(--primary);
}
       
    </style>
</head>
<body>
    <?php if (!empty($login)): ?>
        <p>Вы вошли как: <?= htmlspecialchars($login) ?> (<a href="login.php?action=logout">Выйти</a>)</p>
    <?php else: ?>
        <p><a href="login.php">Войти</a></p>
    <?php endif; ?>

    <?php if (!empty($generated_credentials)): ?>
        <div class="credentials">
            <h3>Ваши данные для входа:</h3>
            <p><strong>Логин:</strong> <?= htmlspecialchars($generated_credentials['login']) ?></p>
            <p><strong>Пароль:</strong> <?= htmlspecialchars($generated_credentials['password']) ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <div class="form-group">
            <label for="full_name">ФИО*</label>
            <input type="text" id="full_name" name="full_name" 
                   value="<?= htmlspecialchars($values['full_name'] ?? '') ?>"
                   class="<?= !empty($errors['full_name']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['full_name'])): ?>
                <div class="error-message">Введите корректное ФИО</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone">Телефон*</label>
            <input type="tel" id="phone" name="phone" 
                   value="<?= htmlspecialchars($values['phone'] ?? '') ?>"
                   class="<?= !empty($errors['phone']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['phone'])): ?>
                <div class="error-message">Введите корректный телефон</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email*</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($values['email'] ?? '') ?>"
                   class="<?= !empty($errors['email']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <div class="error-message">Введите корректный email</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="birth_date">Дата рождения*</label>
            <input type="date" id="birth_date" name="birth_date" 
                   value="<?= htmlspecialchars($values['birth_date'] ?? '') ?>"
                   class="<?= !empty($errors['birth_date']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['birth_date'])): ?>
                <div class="error-message">Введите корректную дату</div>
            <?php endif; ?>
        </div>

     <div class="form-group">
    <label class="input-label">Пол*</label>
    <div class="input-group">
        <div class="input-option">
            <input type="radio" id="gender_male" name="gender" value="male"
                <?= ($values['gender'] ?? '') === 'male' ? 'checked' : '' ?> required>
            <label for="gender_male" class="option-label">Мужской</label>
        </div>
        <div class="input-option">
            <input type="radio" id="gender_female" name="gender" value="female"
                <?= ($values['gender'] ?? '') === 'female' ? 'checked' : '' ?>>
            <label for="gender_female" class="option-label">Женский</label>
        </div>
    </div>
    <?php if (!empty($errors['gender'])): ?>
        <div class="error-message">Выберите пол</div>
    <?php endif; ?>
</div>
        <div class="form-group">
            <label for="languages">Языки программирования*</label>
            <select id="languages" name="languages[]" multiple 
                    class="<?= !empty($errors['languages']) ? 'error' : '' ?>" required>
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= $lang['id'] ?>"
                        <?= in_array($lang['id'], $values['languages'] ?? []) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['languages'])): ?>
                <div class="error-message">Выберите хотя бы один язык</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="biography">Биография</label>
            <textarea id="biography" name="biography"><?= htmlspecialchars($values['biography'] ?? '') ?></textarea>
        </div>
<div class="form-group">
    <div class="input-group">
        <div class="input-option">
            <input type="checkbox" id="contract_agreed" name="contract_agreed"
                <?= ($values['contract_agreed'] ?? false) ? 'checked' : '' ?> required>
            <label for="contract_agreed" class="option-label">С контрактом ознакомлен*</label>
        </div>
    </div>
    <?php if (!empty($errors['contract_agreed'])): ?>
        <div class="error-message">Необходимо согласие</div>
    <?php endif; ?>
</div>

        <button type="submit">Отправить</button>
    </form>
</body>
</html>
<?php

unset($_SESSION['errors'], $_SESSION['generated_credentials']);
?>
