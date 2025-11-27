<?php
session_start();

$ADMIN_USER = 'root';
$ADMIN_PASS = 'btsinfo'; // À CHANGER!

if (isset($_POST['login'])) {
    if ($_POST['username'] === $ADMIN_USER && $_POST['password'] === $ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /');
    exit;
}

if (!isset($_SESSION['logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Login - Dashboard Minetest LXC</title>
        <style>
            body { font-family: Arial; background: #1a1a1a; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-box { background: #2a2a2a; padding: 40px; border-radius: 10px; min-width: 300px; }
            h2 { text-align: center; color: #4CAF50; margin-bottom: 30px; }
            input { width: 100%; padding: 12px; margin: 10px 0; border: none; border-radius: 5px; box-sizing: border-box; }
            button { width: 100%; padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>🐧 Dashboard Minetest LXC</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Connexion</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

function getContainerStatus($container) {
    exec("sudo lxc-info -n $container -s 2>/dev/null | head -n 2 | tail -n 1 | awk '{print $2}'", $output);
    return isset($output[0]) ? trim($output[0]) : 'UNKNOWN';
}

function getContainerIP($container) {
    exec("sudo lxc-info -n $container -iH 2>/dev/null", $output);
    return isset($output[0]) ? trim($output[0]) : 'N/A';
}

function getSystemInfoUptime() {
    exec("uptime -p", $output);
    $info1['uptime'] = $output[0] ?? 'N/A';
    return $info1;
}
function getSystemInfoLoad() {
    exec("uptime | awk -F'load average:' '{print $2}'", $output);
    $info2['load'] = trim($output[0] ?? 'N/A');
    return $info2;
}
function getSystemInfoMemory() {
    exec("free -h | awk '{print $2}' | head -n 2 | tail -n 1", $output);
    $info3['memory'] = $output[0] ?? 'N/A';
    return $info3;
}

function startContainer($container) {
    exec("sudo lxc-start -n $container");
}

function stopContainer($container) {
    exec("sudo lxc-stop -n $container");
}

function startAllContainers() {
    exec("sudo systemctl start lxcStart.service");
}

function stopAllContainers() {
    exec("sudo systemctl start lxcStop.service");
}

// NOUVELLES FONCTIONS POUR LES LOGS ET IP BAN
/**
 * Lit le contenu du fichier IPban.csv.
 * @return array Le tableau des lignes du fichier, ou un tableau vide en cas d'erreur.
 */
function getBannedIPs() {
    // Utiliser sudo pour lire le fichier si nécessaire
    // Le chemin vers le fichier de ban doit être autorisé dans /etc/sudoers (ex: www-data ALL=(root) NOPASSWD: /bin/cat /root/IPban.csv)
    exec("sudo cat /root/IPban.csv 2>&1", $output, $return_var);
    
    if ($return_var !== 0) {
        // En cas d'erreur de lecture (ex: droits, fichier non trouvé), retourner l'erreur ou un message vide
        return ["Erreur de lecture du fichier IPban.csv. Vérifiez les droits sudo. Retour: $return_var. Message: " . implode(" ", $output)];
    }
    
    // Si le fichier existe mais est vide, retourner un message
    if (empty($output)) {
        return ["Le fichier IPban.csv est vide."];
    }
    
    return $output;
}

/**
 * Lit les 10 dernières lignes du fichier de log d'un conteneur spécifique.
 * @param string $container L'ID du conteneur (ex: minetest-classique).
 * @return array Le tableau des 10 dernières lignes, ou un message d'erreur.
 */
function getContainerLogs($container) {
    // Échapper le nom du conteneur pour éviter l'injection de shell
    $safe_container = escapeshellarg($container);
    
    // Construction du chemin du fichier de log en utilisant la variable échappée
    $log_file = "/var/log/minetest-logs/$safe_container.log";
    
    // La commande complète est exécutée en utilisant des guillemets doubles (")
    // pour que PHP interprète $log_file avant l'exécution du shell.
    $command = "/usr/bin/sudo /usr/bin/tail -n 10 $log_file 2>&1";
    
    exec($command, $output, $return_var);

    if ($return_var !== 0) {
        // En cas d'erreur (ex: droits, fichier non trouvé), retourner l'erreur ou un message
        return ["Erreur de lecture du log pour $container. Vérifiez les droits sudo et le chemin du fichier."];
    }
    
    if (empty($output)) {
        return ["Le fichier de log $container.log est vide ou n'existe pas."];
    }
    
    return $output;
}
// FIN NOUVELLES FONCTIONS

$containers = [
    'minetest-classique' => ['name' => '🏔️ Classique', 'port' => 30000, 'ip' => '10.0.3.10'],
    'minetest-creatif' => ['name' => '🎨 Créatif', 'port' => 30001, 'ip' => '10.0.3.5'],
    'minetest-exploration' => ['name' => '🗺️ Exploration', 'port' => 30002, 'ip' => '10.0.3.20'],
    'minetest-survie' => ['name' => '⚔️ Survie', 'port' => 30003, 'ip' => '10.0.3.25'],
    'minetest-perso' => ['name' => '🔥 PvP', 'port' => 30004, 'ip' =>'10.0.3.30']
];

$system_info1 = getSystemInfoUptime();
$system_info2 = getSystemInfoLoad();
$system_info3 = getSystemInfoMemory();

$banned_ips = getBannedIPs(); // Appel de la nouvelle fonction

// Vérification de l'action (démarrer ou arrêter)
if (isset($_POST['start'])) {
    $container_to_start = $_POST['container_id'];
    startContainer($container_to_start);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['stop'])) {
    $container_to_stop = $_POST['container_id'];
    stopContainer($container_to_stop);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Actions globales pour tous les conteneurs
if (isset($_POST['start_all'])) {
    startAllContainers();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['stop_all'])) {
    stopAllContainers();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Minetest LXC</title>
    <meta http-equiv="refresh" content="30">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #1a1a1a; color: #fff; padding: 20px; }
        header { background: #2a2a2a; padding: 20px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        h1 { color: #4CAF50; }
        .logout { background: #f44336; padding: 10px 20px; border-radius: 5px; color: white; text-decoration: none; }
        .system-info { background: #2a2a2a; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .system-info h2 { color: #4CAF50; margin-bottom: 15px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .info-card { background: #333; padding: 15px; border-radius: 8px; }
        .info-label { color: #888; font-size: 0.9em; }
        .info-value { font-size: 1.2em; font-weight: bold; color: #4CAF50; margin-top: 5px; }
        
        /* Section de contrôle global */
        .global-controls { background: #2a2a2a; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .global-controls h2 { color: #4CAF50; margin-bottom: 15px; }
        .global-controls form { display: inline-block; margin: 0 10px; }
        .global-btn { padding: 12px 30px; border-radius: 5px; font-size: 1em; font-weight: bold; border: none; cursor: pointer; transition: all 0.3s; }
        .global-btn.start { background: #4CAF50; color: white; }
        .global-btn.start:hover { background: #45a049; }
        .global-btn.stop { background: #f44336; color: white; }
        .global-btn.stop:hover { background: #da190b; }
        
        .containers-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .container-card { background: #2a2a2a; padding: 20px; border-radius: 10px; border-left: 4px solid #4CAF50; }
        .container-card.stopped { border-left-color: #f44336; }
        .container-header { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .container-title { font-size: 1.3em; font-weight: bold; }
        .status { padding: 5px 15px; border-radius: 20px; font-size: 0.9em; font-weight: bold; }
        .status.running { background: #4CAF50; }
        .status.stopped { background: #f44336; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #333; }
        .detail-label { color: #888; }
        .detail-value { font-weight: bold; color: #4CAF50; }
        .action { padding: 5px 15px; border-radius: 20px; font-size: 0.9em; font-weight: bold; border: none; cursor: pointer; }

        /* Style pour les logs et IP ban */
        .log-section { background: #2a2a2a; padding: 20px; border-radius: 10px; margin-top: 20px; }
        .log-section h2 { color: #4CAF50; margin-bottom: 15px; }
        .log-content { background: #1a1a1a; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre; font-family: monospace; font-size: 0.9em; color: #ddd; max-height: 400px; }
        .ip-list { background: #1a1a1a; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.9em; color: #ddd; }
        .ip-list div { padding: 3px 0; border-bottom: 1px dotted #333; }
        .ip-list div:last-child { border-bottom: none; }
        .logs-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
    </style>
</head>
<body>
    <header>
        <h1>🐧 Dashboard Minetest LXC</h1>
        <a href="?logout" class="logout">Déconnexion</a>
    </header>

    <div class="system-info">
        <h2>📊 Serveur Central</h2>
        <div class="info-grid">
            <div class="info-card">
                <div class="info-label">Uptime</div>
                <div class="info-value"><?= htmlspecialchars($system_info1['uptime']) ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">Load Average</div>
                <div class="info-value"><?= htmlspecialchars($system_info2['load']) ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">Mémoire use</div>
                <div class="info-value"><?= htmlspecialchars($system_info3['memory']) ?></div>
            </div>
        </div>
    </div>

    <div class="global-controls">
        <h2>Contrôle Global des Conteneurs :</h2>
        <form method="POST" style="display: inline;">
            <button type="submit" name="start_all" class="global-btn start">Démarrer TOUS les conteneurs</button>
        </form>
        <form method="POST" style="display: inline;">
            <button type="submit" name="stop_all" class="global-btn stop">Arrêter TOUS les conteneurs</button>
        </form>
    </div>

    <h2 style="margin-bottom: 20px; color: #4CAF50;"> Conteneurs Minetest</h2>
    <div class="containers-grid">
        <?php foreach ($containers as $container_id => $data):
            $status = getContainerStatus($container_id);
            $ip = getContainerIP($container_id);
            $is_running = ($status === 'RUNNING');
        ?>
        <div class="container-card <?= $is_running ? '' : 'stopped' ?>">
            <div class="container-header">
                <div class="container-title"><?= $data['name'] ?></div>
                <div class="status <?= $is_running ? 'running' : 'stopped' ?>">
                    <?= $is_running ? '✓ RUNNING' : '✗ STOPPED' ?>
                </div>
            </div>
            <div class="detail-row">
                <span class="detail-label">Conteneur:</span>
                <span class="detail-value"><?= $container_id ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">IP interne:</span>
                <span class="detail-value"><?= $ip ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Port externe:</span>
                <span class="detail-value"><?= $data['port'] ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Port interne:</span>
                <span class="detail-value">30000</span>
            </div>

            <form method="POST" style="margin-top: 10px;">
                <input type="hidden" name="container_id" value="<?= $container_id ?>">
                <?php if ($is_running): ?>
                    <button class="action" type="submit" name="stop" style="background-color: #f44336; color: white;">Arrêter</button>
                <?php else: ?>
                    <button class="action" type="submit" name="start" style="background-color: #4CAF50; color: white;">Démarrer</button>
                <?php endif; ?>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="log-section">
        <h2>⛔ Liste des IPs Bannies (/root/IPban.csv)</h2>
        <div class="ip-list">
            <?php if (!empty($banned_ips)): ?>
                <?php foreach ($banned_ips as $line): ?>
                    <div><?= htmlspecialchars($line) ?></div>
                <?php endforeach; ?>
            <?php else: ?>
                <div>Aucune IP bannie trouvée ou erreur de lecture.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="log-section">
        <h2>📄 Dernières Lignes des Logs des Conteneurs (10 dernières lignes)</h2>
        <div class="logs-grid">
            <?php foreach ($containers as $container_id => $data):
                $logs = getContainerLogs($container_id);
            ?>
            <div class="info-card">
                <h3 style="color: #eee; margin-bottom: 10px;"><?= $data['name'] ?> (<?= $container_id ?>)</h3>
                <div class="log-content">
                    <?php if (!empty($logs)): ?>
                        <?= implode("\n", array_map('htmlspecialchars', $logs)) ?>
                    <?php else: ?>
                        Erreur lors de la récupération des logs.
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div style="text-align: center; margin-top: 30px; color: #888;">
        <p>Actualisation automatique toutes les 30 secondes</p>
    </div>
</body>
</html>