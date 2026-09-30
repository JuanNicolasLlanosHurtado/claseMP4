<?php
require_once 'conexion.php';
$seccion_actual = isset($_GET['seccion']) ? $_GET['seccion'] : 'inicio';
$mensaje_db = ""; $mensaje_voto = "";
try {
    $conexion->exec("CREATE TABLE IF NOT EXISTS votos_goat (id INT AUTO_INCREMENT PRIMARY KEY, jugador VARCHAR(20) NOT NULL, fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $conexion->exec("CREATE TABLE IF NOT EXISTS contactos (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, email VARCHAR(100), mensaje TEXT, fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
} catch (Exception $e) {}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre_contacto'])) {
    $nombre = trim($_POST['nombre_contacto']); $email = trim($_POST['email'] ?? ''); $msg = trim($_POST['mensaje'] ?? '');
    if (!empty($nombre)) {
        try { $stmt = $conexion->prepare("INSERT INTO contactos (nombre, email, mensaje) VALUES (:nombre, :email, :mensaje)"); $stmt->execute([':nombre'=>$nombre, ':email'=>$email, ':mensaje'=>$msg]); $mensaje_db = "<div class='alerta exito'>¡Gracias $nombre! Mensaje guardado.</div>"; } catch (Exception $e) { $mensaje_db = "<div class='alerta error'>Error: ".$e->getMessage()."</div>"; }
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['voto_goat'])) {
    $voto = $_POST['voto_goat'] === 'messi' ? 'messi' : 'cr7';
    try { $stmt = $conexion->prepare("INSERT INTO votos_goat (jugador) VALUES (:jugador)"); $stmt->execute([':jugador'=>$voto]); $mensaje_voto = "<div class='alerta exito'>¡Voto por ".($voto=='messi'?'MESSI':'CRISTIANO')." registrado!</div>"; } catch (Exception $e) {}
}
$votos_messi = 0; $votos_cr7 = 0;
try { $votos_messi = $conexion->query("SELECT COUNT(*) FROM votos_goat WHERE jugador='messi'")->fetchColumn(); $votos_cr7 = $conexion->query("SELECT COUNT(*) FROM votos_goat WHERE jugador='cr7'")->fetchColumn(); } catch (Exception $e) {}
$total_votos = $votos_messi + $votos_cr7;
$perc_messi = $total_votos ? round($votos_messi*100/$total_votos) : 50;
$perc_cr7 = $total_votos ? 100 - $perc_messi : 50;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CR7 vs Messi - Estadio Edition</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{ --azul:#00b4ff; --azul2:#004e92; --rojo:#ff1844; --rojo2:#8a0000; --dorado:#ffcc00; --verde:#00ff88; }
*{box-sizing:border-box}
body{
  font-family:'Inter',sans-serif; margin:0; color:#fff;
  /* FONDO ESTADIO */
  background: url('img/estadio.jpg'), linear-gradient(135deg,#0f172a,#1e3a5f);
  background-size: cover; background-attachment: fixed; background-position: center;
  background-blend-mode: overlay;
  min-height:100vh;
}
body::before{
  content:''; position:fixed; inset:0;
  background: radial-gradient(circle at center, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.75) 100%);
  z-index:-1;
}
header{
  background: linear-gradient(90deg, var(--azul2) 0%, #1e3a8a 25%, #7c0000 75%, var(--rojo2) 100%);
  padding:16px 0; position:sticky; top:0; z-index:100; box-shadow:0 6px 30px rgba(0,0,0,.6);
  border-bottom: 3px solid var(--dorado);
}
.header-inner{max-width:1250px; margin:0 auto; display:flex; justify-content:space-between; align-items:center; padding:0 20px}
.logo{font-family:'Montserrat'; font-weight:900; font-size:2em; letter-spacing:-1px; text-shadow:0 2px 10px rgba(0,0,0,.5)}
.logo span{background: linear-gradient(90deg,var(--dorado),#fff); -webkit-background-clip:text; -webkit-text-fill-color:transparent}
nav ul{list-style:none; display:flex; gap:10px; margin:0; padding:0; flex-wrap:wrap}
nav a{color:white; text-decoration:none; font-weight:700; padding:10px 18px; border-radius:30px; transition:.3s; background:rgba(255,255,255,0.12); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.2)}
nav a:hover, nav a.activo{background:white; color:#0f172a; transform:translateY(-2px); box-shadow:0 8px 20px rgba(0,0,0,.3)}
#contenedor-principal{max-width:1250px; margin:30px auto; padding:0 20px}
.card-base{background: rgba(255,255,255,0.96); color:#1e293b; border-radius:20px; padding:32px; box-shadow:0 15px 50px rgba(0,0,0,.4); margin-bottom:30px; border:1px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px);}
.card-base h2{font-family:'Montserrat'; font-weight:900; font-size:2em; background: linear-gradient(90deg,var(--azul2),var(--rojo2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; margin-top:0}
.hero{
  background: linear-gradient(135deg, rgba(0,78,146,0.9) 0%, rgba(124,0,0,0.9) 100%), url('img/estadio.jpg');
  background-size:cover; background-position:center; color:white; border-radius:24px; padding:55px 40px;
  display:grid; grid-template-columns:1.2fr .8fr; gap:30px; align-items:center; border:2px solid rgba(255,255,255,0.2); box-shadow:0 20px 60px rgba(0,0,0,.6); position:relative; overflow:hidden;
}
@media(max-width:900px){.hero{grid-template-columns:1fr}}
.hero::after{content:'⚽'; position:absolute; font-size:300px; opacity:0.05; right:-50px; top:-50px; transform:rotate(15deg)}
.hero h1{font-family:'Montserrat'; font-weight:900; font-size:3.4em; line-height:.9; margin:0; text-shadow:0 4px 20px rgba(0,0,0,.6)}
.hero p{color:#e2e8f0; font-size:1.25em; font-weight:600}
.btn{display:inline-block; padding:14px 26px; border-radius:12px; text-decoration:none; font-weight:800; margin:10px 10px 0 0; transition:.3s; box-shadow:0 6px 20px rgba(0,0,0,.3); border:none; cursor:pointer}
.btn-azul{background: linear-gradient(135deg,var(--azul),var(--azul2)); color:white}
.btn-rojo{background: linear-gradient(135deg,var(--rojo),var(--rojo2)); color:white}
.btn-dorado{background: linear-gradient(135deg,var(--dorado),#ff8c00); color:#000}
.btn:hover{transform:translateY(-3px) scale(1.02); box-shadow:0 12px 30px rgba(0,0,0,.4)}
.historia-grid{display:grid; grid-template-columns:1fr 1fr; gap:30px}
@media(max-width:900px){.historia-grid{grid-template-columns:1fr}}
.card-historia{border-radius:20px; overflow:hidden; background:white; box-shadow:0 15px 40px rgba(0,0,0,.3); transition:.4s; border:3px solid transparent; display:flex; flex-direction:column}
.card-historia:hover{transform:translateY(-8px) scale(1.02); box-shadow:0 25px 60px rgba(0,0,0,.5)}
.card-messi{border-image: linear-gradient(135deg,var(--azul),#00f2ff) 1; border-color:var(--azul)}
.card-cr7{border-color:var(--rojo)}
.img-container{height:420px; overflow:hidden; background:#0f172a; position:relative}
.img-container img{width:100%; height:100%; object-fit:cover; object-position:center top; transition:.6s}
.card-historia:hover img{transform:scale(1.08)}
.tag{position:absolute; top:14px; left:14px; padding:8px 14px; border-radius:30px; font-weight:900; color:white; font-size:.85em; z-index:2; box-shadow:0 4px 15px rgba(0,0,0,.4); letter-spacing:.5px}
.tag.messi{background: linear-gradient(135deg,var(--azul),var(--azul2))} .tag.cr7{background: linear-gradient(135deg,var(--rojo),var(--rojo2))}
.card-body{padding:24px} .card-body h3{font-family:'Montserrat'; font-weight:800; font-size:1.5em; margin:0 0 12px}
.stat-cards{display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:18px; margin:24px 0}
.stat-card{background: linear-gradient(135deg, #ffffff, #f1f5f9); border-radius:16px; padding:20px; text-align:center; border:2px solid; box-shadow:0 8px 20px rgba(0,0,0,.1)}
.stat-card.azul{border-color:var(--azul); background: linear-gradient(135deg,#e0f2ff,#ffffff)} .stat-card.rojo{border-color:var(--rojo); background: linear-gradient(135deg,#ffe0e6,#ffffff)}
.stat-card .num{font-family:'Montserrat'; font-size:2.4em; font-weight:900}
table{width:100%; border-collapse:collapse; margin:20px 0; border-radius:14px; overflow:hidden; box-shadow:0 8px 25px rgba(0,0,0,.1)} th{padding:16px; text-align:left; color:white; font-weight:800} .th-messi{background: linear-gradient(90deg,var(--azul2),var(--azul))} .th-cr7{background: linear-gradient(90deg,var(--rojo2),var(--rojo))} td{padding:14px; border-bottom:1px solid #f1f5f9; color:#334155} tr:nth-child(even){background:#f8fafc}
.videos-finales{background: linear-gradient(135deg, rgba(15,23,42,0.95), rgba(30,58,138,0.9)); border-radius:24px; padding:35px; border:2px solid var(--dorado); box-shadow:0 20px 60px rgba(0,0,0,.6); margin-top:30px}
.videos-finales h2{color:white !important; -webkit-text-fill-color:white !important; text-align:center; font-size:2.4em}
.video-grid-final{display:grid; grid-template-columns:1fr 1fr; gap:28px; margin-top:25px}
@media(max-width:900px){.video-grid-final{grid-template-columns:1fr}}
.video-box{background: rgba(255,255,255,0.98); border-radius:18px; padding:18px; box-shadow:0 10px 30px rgba(0,0,0,.4); border:3px solid transparent}
.video-box.messi-box{border-color:var(--azul)} .video-box.cr7-box{border-color:var(--rojo)}
.video-box h3{font-family:'Montserrat'; font-weight:900; margin:0 0 12px; text-align:center; font-size:1.4em}
.video-box video, .video-box iframe{width:100%; height:300px; border-radius:12px; background:#000; border:none}
.upload-hint{background:#f1f5f9; padding:12px; border-radius:10px; margin-top:12px; font-size:.9em; color:#475569; border-left:4px solid var(--dorado)}
.barra-voto{height:22px; background:#e2e8f0; border-radius:12px; overflow:hidden; display:flex; margin:14px 0; box-shadow:inset 0 2px 5px rgba(0,0,0,.2)} .barra-messi{background: linear-gradient(90deg,var(--azul2),var(--azul))} .barra-cr7{background: linear-gradient(90deg,var(--rojo2),var(--rojo))}
.alerta{padding:14px 18px; border-radius:12px; margin:14px 0; font-weight:700} .exito{background:#dcfce7; color:#166534; border:2px solid #86efac} .error{background:#fee2e2; color:#991b1b}
input, textarea{width:100%; padding:14px; border:2px solid #cbd5e1; border-radius:10px; margin:8px 0 14px; font-weight:600} input:focus, textarea:focus{outline:none; border-color:var(--azul); box-shadow:0 0 0 3px rgba(0,180,255,.2)}
footer{background: linear-gradient(90deg,#020617,#0f172a); color:#94a3b8; text-align:center; padding:35px 20px; margin-top:50px; border-top:3px solid var(--dorado)}
</style>
</head>
<body>
<header><div class="header-inner"><div class="logo">CR7 <span>VS</span> MESSI</div><nav><ul><li><a href="?seccion=inicio" class="<?=$seccion_actual=='inicio'?'activo':''?>">🏟️ Inicio</a></li><li><a href="?seccion=estadisticas_personal" class="<?=$seccion_actual=='estadisticas_personal'?'activo':''?>">⭐ Personal</a></li><li><a href="?seccion=estadisticas_clubes" class="<?=$seccion_actual=='estadisticas_clubes'?'activo':''?>">🏆 Clubes</a></li><li><a href="?seccion=palmares" class="<?=$seccion_actual=='palmares'?'activo':''?>">👑 Palmarés</a></li><li><a href="?seccion=contacto" class="<?=$seccion_actual=='contacto'?'activo':''?>">🐐 Vota GOAT</a></li></ul></nav></div></header>

<div id="contenedor-principal">
<?php switch($seccion_actual){ case 'inicio': ?>
<div class="hero"><div><h1>EL DEBATE<br>DEL SIGLO</h1><p>Dos caminos a la gloria. Un estadio como testigo. ¿Azul o Rojo? ¿Messi o Cristiano?</p><a class="btn btn-azul" href="?seccion=estadisticas_personal">⭐ Ver Stats</a><a class="btn btn-rojo" href="#videos-final">🎬 Ver Videos</a></div>
<div style="text-align:center"><img src="img/messi.jpg" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/b/b4/Lionel-Messi-Argentina-2022-FIFA-World-Cup.jpg'" style="width:47%; border-radius:16px; height:260px; object-fit:cover; border:3px solid var(--azul); box-shadow:0 10px 30px rgba(0,0,0,.5)"><img src="img/cristiano.jpg" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/8/8c/Cristiano_Ronaldo_2018.jpg'" style="width:47%; border-radius:16px; height:260px; object-fit:cover; border:3px solid var(--rojo); margin-left:8px; box-shadow:0 10px 30px rgba(0,0,0,.5)"></div></div>

<div class="card-base"><h2>Historia Completa</h2><div class="historia-grid">
<div class="card-historia card-messi"><div class="img-container"><span class="tag messi">ARGENTINA #10</span><img src="img/messi.jpg" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/b/b4/Lionel-Messi-Argentina-2022-FIFA-World-Cup.jpg'"></div><div class="card-body"><h3 style="color:var(--azul2)">Lionel Messi - La Pulga</h3><p><b>24 junio 1987, Rosario.</b> Con déficit de crecimiento, Barça lo lleva a La Masía. 672 goles, 10 Ligas, 4 Champions, 8 Balones de Oro.</p><p><b>18 Dic 2022:</b> Campeón del Mundo Qatar. Gol en final. MVP.</p></div></div>
<div class="card-historia card-cr7"><div class="img-container"><span class="tag cr7">PORTUGAL #7</span><img src="img/cristiano.jpg" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/8/8c/Cristiano_Ronaldo_2018.jpg'"></div><div class="card-body"><h3 style="color:var(--rojo2)">Cristiano Ronaldo - El Bicho</h3><p><b>5 feb 1985, Madeira.</b> Sporting → Man Utd → Real Madrid 450 goles/438 partidos → Juventus → Al-Nassr. Máximo goleador histórico 890+.</p><p><b>2016:</b> Eurocopa con Portugal. Salto 2.93m, potencia total.</p></div></div>
</div></div>
<?php break; case 'estadisticas_personal': ?>
<div class="card-base"><h2>⭐ Estadísticas a Nivel Personal</h2><div class="stat-cards"><div class="stat-card azul"><div class="num" style="color:var(--azul2)">8 - 5</div><div class="label">Balón de Oro (Messi-CR7)</div></div><div class="stat-card rojo"><div class="num" style="color:var(--rojo2)">850+ / 895+</div><div class="label">Goles Totales</div></div></div><table><tr><th class="th-messi">Premio</th><th class="th-messi">Messi</th><th class="th-cr7">CR7</th></tr><tr><td>Balón Oro</td><td>8 RÉCORD</td><td>5</td></tr><tr><td>Mundial</td><td>1 (2022)</td><td>0</td></tr><tr><td>Goles Sel.</td><td>112+</td><td>133+ RÉCORD</td></tr></table><canvas id="radarChart" height="260"></canvas><script>new Chart(document.getElementById('radarChart'),{type:'radar',data:{labels:['Regate',[STRIPPED] Libre'],datasets:[{label:'Messi',data:[95,[STRIPPED]
<?php break; case 'estadisticas_clubes': ?>
<div class="card-base"><h2>🏆 Estadísticas a Nivel de Clubes</h2><table><tr><th class="th-messi">Club</th><th class="th-messi">Messi</th><th class="th-cr7">CR7</th></tr><tr><td>Barça / Real Madrid</td><td>672 en 778</td><td>450 en 438</td></tr><tr><td>Champions</td><td>4 títulos</td><td>5 títulos (140 goles)</td></tr></table><canvas id="barChart" height="260"></canvas><script>new Chart(document.getElementById('barChart'),{type:'bar',data:{labels:['Barça',[STRIPPED] Utd','Juve'],datasets:[{label:'Messi',data:[672,[STRIPPED]
<?php break; case 'palmares': ?>
<div class="card-base"><h2>👑 Palmarés Completo</h2><div style="display:grid; grid-template-columns:1fr 1fr; gap:20px"><div style="background: linear-gradient(135deg,#e0f2ff,#fff); padding:20px; border-radius:14px; border:2px solid var(--azul)"><h3 style="color:var(--azul2)">Messi 44 Títulos</h3><ul><li>Mundial 2022</li><li>4x Champions</li><li>10x LaLiga</li></ul></div><div style="background: linear-gradient(135deg,#ffe0e6,#fff); padding:20px; border-radius:14px; border:2px solid var(--rojo)"><h3 style="color:var(--rojo2)">CR7 35 Títulos</h3><ul><li>Euro 2016</li><li>5x Champions</li><li>3x Premier, 2x LaLiga, 2x Serie A</li></ul></div></div></div>
<?php break; case 'contacto': ?>
<div class="card-base"><h2>🐐 Vota por el GOAT</h2><?=$mensaje_voto?><div style="background:#f8fafc; padding:20px; border-radius:14px; border:2px solid var(--dorado)"><h3><?=$total_votos?> votos</h3><div style="display:flex; justify-content:space-between; font-weight:900"><span style="color:var(--azul2)">MESSI <?=$perc_messi?>%</span><span style="color:var(--rojo2)"><?=$perc_cr7?>% CR7</span></div><div class="barra-voto"><div class="barra-messi" style="width:<?=$perc_messi?>%"></div><div class="barra-cr7" style="width:<?=$perc_cr7?>%"></div></div><form method="POST"><label><input type="radio" name="voto_goat" value="messi" checked> Messi 🐐</label><br><label><input type="radio" name="voto_goat" value="cr7"> Cristiano 🐐</label><br><br><button class="btn btn-dorado">Votar Ahora</button></form></div><h3 style="margin-top:20px">Deja tu opinión</h3><?=$mensaje_db?><form method="POST"><input name="nombre_contacto" placeholder="Tu nombre" required><textarea name="mensaje" rows="3" placeholder="¿Por qué es el mejor?"></textarea><button class="btn btn-azul">Enviar</button></form></div>
<?php break; } ?>


<div id="videos-final" class="videos-finales">
<h2>🎬 VIDEOS EXCLUSIVOS - ELIGE TU FAVORITO</h2>

<div class="video-grid-final">

<div class="video-box messi-box">
<h3 style="color:var(--azul2)">🔵 VIDEO DE MESSI - La Magia</h3>

<video controls poster="img/messi.jpg">
<source src="videos/messi.mp4" type="video/mp4">
<source src="videos/messi.webm" type="video/webm">

</video>

<div style="margin-top:12px">

</div>
<div class="upload-hint">

</div>
</div>

<div class="video-box cr7-box">
<h3 style="color:var(--rojo2)">🔴 VIDEO DE CRISTIANO - La Potencia</h3>
<video controls poster="img/cristiano.jpg">
<source src="videos/cristiano.mp4" type="video/mp4">
<source src="videos/cristiano.webm" type="video/webm">

</video>



</div>
</div>

</div>
</div>

</div>
<footer><p style="font-weight:900; color:white; font-size:1.2em">🏟️ CR7 VS MESSI - ESTADIO EDITION </p></footer>
</body>
</html>
