<?php
/**
 * Page détail d'un modèle ORCA V2
 */
require_once 'includes/config.php';

// Récupérer le slug du modèle
$slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

if (empty($slug)) {
    redirect('modeles.php');
}

// Récupérer le modèle
$modele = getModeleBySlug($slug);

if (!$modele) {
    header('HTTP/1.0 404 Not Found');
    redirect('modeles.php');
}

// Récupérer les modèles similaires
$stmt = $pdo->prepare("SELECT * FROM modeles WHERE id != ? AND is_active = 1 AND (style = ? OR nb_etages = ?) ORDER BY RAND() LIMIT 2");
$stmt->execute([$modele['id'], $modele['style'], $modele['nb_etages']]);
$modeles_similaires = $stmt->fetchAll();

// SEO
$page_title = clean($modele['nom']) . ' - ' . clean($modele['surface_habitable']) . 'm² | Maisons ORCA';
$page_description = clean(substr($modele['description'], 0, 160));

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <div class="modele-badges" style="justify-content: center; margin-bottom: var(--space-4);">
            <?php echo getEtageBadge($modele['nb_etages']); ?>
            <?php echo getStyleBadge($modele['style']); ?>
        </div>
        <h1 class="page-header-title"><?php echo clean($modele['nom']); ?></h1>
        <p class="page-header-text"><?php echo clean($modele['slogan']); ?></p>
    </div>
</header>

<section class="section">
    <div class="container">
        <div class="modele-detail">
            <!-- Galerie -->
            <div class="modele-gallery">
                <div class="modele-image-main" onclick="openLightbox(0)">
                    <img id="main-image" src="<?php echo url('uploads/maisons/' . ($modele['image_principale'] ?? 'default.jpg')); ?>" alt="<?php echo clean($modele['nom']); ?>">
                    <div class="zoom-hint">🔍 Cliquez pour agrandir</div>
                </div>
                <div class="modele-thumbs">
                    <?php 
                    $galerie = json_decode($modele['images_galerie'] ?? '[]', true);
                    $all_images = array_merge([$modele['image_principale']], $galerie);
                    $all_images = array_filter($all_images);
                    foreach ($all_images as $index => $img): 
                    ?>
                    <div class="modele-thumb <?php echo $index === 0 ? 'active' : ''; ?>" onclick="changeImage(<?php echo $index; ?>)">
                        <img src="<?php echo url('uploads/maisons/' . $img); ?>" data-full="<?php echo url('uploads/maisons/' . $img); ?>" alt="">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($all_images) > 4): ?>
                <div style="text-align: center; margin-top: var(--space-3);">
                    <button onclick="openLightbox(0)" class="btn btn-outline btn-sm">
                        📷 Voir les <?php echo count($all_images); ?> photos
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Informations -->
            <div>
                <div class="modele-info">
                    <h2 style="margin-bottom: var(--space-4);">Description</h2>
                    <p><?php echo nl2br(clean($modele['description'])); ?></p>
                    
                    <h3 style="margin-top: var(--space-8); margin-bottom: var(--space-4);">Points forts</h3>
                    <ul style="list-style: none;">
                        <?php foreach (explode('\n', $modele['points_forts']) as $point): ?>
                        <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-3);">
                            <span style="color: var(--color-primary);">✓</span>
                            <span><?php echo clean($point); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Spécifications -->
                <div class="modele-specs" style="margin-top: var(--space-6);">
                    <div class="modele-spec">
                        <div class="modele-spec-value"><?php echo formatSurface($modele['surface_habitable']); ?></div>
                        <div class="modele-spec-label">Surface habitable</div>
                    </div>
                    <div class="modele-spec">
                        <div class="modele-spec-value"><?php echo $modele['nb_chambres']; ?></div>
                        <div class="modele-spec-label">Chambres</div>
                    </div>
                    <div class="modele-spec">
                        <div class="modele-spec-value"><?php echo $modele['nb_salles_bain']; ?></div>
                        <div class="modele-spec-label">Salles de bain</div>
                    </div>
                </div>
                
                <!-- Prix -->
                <div class="modele-price-box" style="margin-top: var(--space-6);">
                    <?php 
                    $prix = $modele['prix_afficher'] ?? '';
                    // Si le prix contient déjà "À partir de", on l'affiche tel quel
                    // Sinon on ajoute le label
                    if (stripos($prix, 'partir') === false): 
                    ?>
                    <div class="modele-price-label">À partir de</div>
                    <?php endif; ?>
                    <div class="modele-price"><?php echo clean($prix); ?></div>
                </div>
                
                <!-- CTA -->
                <div style="margin-top: var(--space-6); display: flex; gap: var(--space-4); flex-wrap: wrap;">
                    <a href="<?php echo url('contact.php?modele=' . $modele['slug']); ?>" class="btn btn-primary btn-lg" style="flex: 1; min-width: 200px;">Demander un devis</a>
                    <a href="tel:0344000000" class="btn btn-outline btn-lg" style="flex: 1; min-width: 200px;">Nous appeler</a>
                </div>
                
                <!-- Plan PDF -->
                <?php if (!empty($modele['plan_pdf'])): ?>
                <div style="margin-top: var(--space-6); text-align: center;">
                    <a href="<?php echo url('uploads/plans/' . $modele['plan_pdf']); ?>" target="_blank" class="btn btn-outline">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: var(--space-2);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Télécharger le plan
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Section: Options et inclusions -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Ce qui est inclus dans le prix</h2>
            <p class="section-text">Tout ce dont vous avez besoin pour emménager sereinement</p>
        </div>
        
        <div class="grid grid-3">
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: var(--space-4);">Structure</h3>
                <ul style="list-style: none; font-size: var(--text-sm);">
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Fondations superficielles</li>
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Murs en briques ou parpaings</li>
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Charpente traditionnelle</li>
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Couverture tuiles ou ardoises</li>
                    <li style="padding: var(--space-2) 0;">✓ Menuiseries PVC ou ALU</li>
                </ul>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: var(--space-4);">Intérieur</h3>
                <ul style="list-style: none; font-size: var(--text-sm);">
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Cloisons et plafonds</li>
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Carrelage séjour/cuisine</li>
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Parquet ou moquette chambres</li>
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Cuisine équipée (meubles + électro)</li>
                    <li style="padding: var(--space-2) 0;">✓ Salle de bain complète</li>
                </ul>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: var(--space-4);">Équipements</h3>
                <ul style="list-style: none; font-size: var(--text-sm);">
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Chauffage gaz + eau chaude</li>
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Volets roulants électriques</li>
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Porte de garage sectionnelle</li>
                    <li style="padding: var(--space-2) 0; border-bottom: 1px solid var(--color-gray-light);">✓ Portail + interphone</li>
                    <li style="padding: var(--space-2) 0;">✓ Jardinet clôturé</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Section: Modèles similaires -->
<?php if (!empty($modeles_similaires)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Vous pourriez aussi aimer</h2>
        </div>
        
        <div class="modeles-grid" style="grid-template-columns: repeat(2, 1fr);">
            <?php foreach ($modeles_similaires as $similaire): ?>
            <article class="card">
                <div class="card-image">
                    <img src="<?php echo url('uploads/maisons/' . ($similaire['image_principale'] ?? 'default.jpg')); ?>" alt="<?php echo clean($similaire['nom']); ?>">
                    <div class="card-badges">
                        <?php echo getEtageBadge($similaire['nb_etages']); ?>
                        <?php echo getStyleBadge($similaire['style']); ?>
                    </div>
                </div>
                <div class="card-content">
                    <h3 class="card-title"><?php echo clean($similaire['nom']); ?></h3>
                    <p class="card-text"><?php echo clean($similaire['slogan']); ?></p>
                    <div class="card-footer">
                        <span class="card-price"><?php echo clean($similaire['prix_afficher']); ?></span>
                        <a href="<?php echo url('modele.php?slug=' . $similaire['slug']); ?>" class="btn btn-outline btn-sm">Découvrir</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Section: Chatbot contextualisé -->
<section class="section section-alt" id="devis">
    <div class="container container-narrow">
        <div class="section-header">
            <p class="section-subtitle">Devis gratuit</p>
            <h2 class="section-title">Intéressé par <?php echo htmlspecialchars($modele['nom']); ?> ?</h2>
            <p class="section-text">Discutez avec notre assistant pour obtenir une estimation personnalisée</p>
        </div>

        <?php
        $m_nom = htmlspecialchars($modele['nom']);
        $m_surface = $modele['surface_habitable'];
        $m_chambres = $modele['nb_chambres'];
        $m_prix = $modele['prix_afficher'] ?: '';
        $m_etage = $modele['nb_etages'] === 'plain-pied' ? 'plain-pied' : 'avec étage';
        ?>

        <div id="mc-chatbot" style="background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.12);overflow:hidden;max-width:520px;margin:0 auto;">
            <div style="background:linear-gradient(135deg,#1a5653,#0f3d3a);color:#fff;padding:16px 20px;display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;">💬</div>
                <div><div style="font-weight:700;font-size:15px;">Devis <?php echo $m_nom; ?></div><div style="font-size:11px;opacity:.7;">Estimation gratuite en 2 min</div></div>
            </div>
            <div id="mc-msgs" style="min-height:250px;max-height:380px;overflow-y:auto;padding:16px;background:#f5f7f9;"></div>
            <div id="mc-form" style="display:none;padding:14px 16px;background:#fff;border-top:1px solid #eee;">
                <div style="font-weight:600;font-size:13px;margin-bottom:10px;color:#1a5653;">📋 Vos coordonnées</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                    <input id="mf-p" placeholder="Prénom *" style="padding:9px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;outline:none;">
                    <input id="mf-n" placeholder="Nom *" style="padding:9px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;outline:none;">
                </div>
                <input id="mf-e" type="email" placeholder="Email *" style="width:100%;padding:9px 10px;margin:6px 0;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;font-size:13px;outline:none;">
                <input id="mf-t" type="tel" placeholder="Téléphone *" style="width:100%;padding:9px 10px;margin:0 0 6px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;font-size:13px;outline:none;">
                <div id="mc-err" style="display:none;color:#e74c3c;font-size:12px;margin-bottom:6px;"></div>
                <button id="mc-submit" style="width:100%;padding:12px;background:linear-gradient(135deg,#1a5653,#0f3d3a);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:15px;font-weight:700;">Recevoir mon devis pour <?php echo $m_nom; ?></button>
            </div>
            <div id="mc-inp" style="padding:12px 16px;background:#fff;border-top:1px solid #eee;display:flex;gap:8px;">
                <input id="mc-input" type="text" placeholder="Votre question..." style="flex:1;padding:10px 14px;border:1px solid #ddd;border-radius:24px;font-size:13px;outline:none;" autocomplete="off">
                <button id="mc-send" style="padding:10px 16px;background:#1a5653;color:#fff;border:none;border-radius:24px;cursor:pointer;font-size:13px;font-weight:700;">Envoyer</button>
            </div>
        </div>
    </div>
</section>

<script>
// Chatbot inline contextualisé pour le modèle
(function() {
    var mcId = null, mcStep = 1;
    var apiUrl = '<?php echo url("chatbot/api.php"); ?>';
    var modelName = <?php echo json_encode($m_nom); ?>;
    var modelSurface = <?php echo json_encode($m_surface); ?>;
    var modelChambres = <?php echo json_encode($m_chambres); ?>;
    var modelPrix = <?php echo json_encode($m_prix); ?>;
    var modelEtage = <?php echo json_encode($m_etage); ?>;

    function q(id) { return document.getElementById(id); }

    // Init avec message contextuel
    showTyping();
    post('action=init', function(d) {
        hideTyping();
        if (d.error) { addMsg(d.error, 'bot'); return; }
        mcId = d.conversation_id;
        mcStep = d.step || 1;

        // Message contextuel basé sur le modèle
        var welcome = "Vous regardez **" + modelName + "** ! Excellent choix 👍\n\n" +
            "📐 " + modelSurface + "m², " + modelChambres + " chambres, " + modelEtage + "\n" +
            "💰 " + modelPrix + "\n\n" +
            "Comment puis-je vous aider ?";
        addMsg(welcome, 'bot');
        showBtns([
            {label: '💰 Obtenir un devis pour ce modèle', value: 'go_form', next: 50},
            {label: '🌿 Trouver un terrain adapté', value: 'go_terrain', next: 20},
            {label: '❓ J\'ai une question', value: 'go_question', next: 40}
        ]);
    });

    q('mc-send').onclick = function() { var t=q('mc-input').value.trim(); if(t){q('mc-input').value='';send(t);} };
    q('mc-input').onkeypress = function(e) { if(e.key==='Enter'){var t=q('mc-input').value.trim();if(t){q('mc-input').value='';send(t);}} };
    q('mc-submit').onclick = submitForm;

    function send(val, label) {
        addMsg(label||val, 'user'); clearBtns(); hideForm(); showTyping();
        post('action=message&conversation_id='+mcId+'&message='+encodeURIComponent(val), function(d) {
            hideTyping();
            if(d.error){addMsg(d.error,'bot');return;}
            mcStep=d.step||mcStep;
            if(d.message)addMsg(d.message,'bot');
            if(d.type==='form')showForm();
            else if(d.type==='results_then_form')setTimeout(showForm,1500);
            else if(d.type==='final'){hideForm();hideInput();if(d.options)showBtns(d.options);}
            else if(d.options)showBtns(d.options);
        });
    }
    function submitForm() {
        var p=q('mf-p').value.trim(),n=q('mf-n').value.trim(),e=q('mf-e').value.trim(),t=q('mf-t').value.trim(),err=q('mc-err');
        var errs=[];
        if(!p||p.length<2)errs.push('Prénom');if(!n||n.length<2)errs.push('Nom');
        if(!e||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e))errs.push('Email');
        if(!t||!/^0[1-9][\s.-]?(\d{2}[\s.-]?){4}$/.test(t))errs.push('Téléphone');
        if(errs.length){err.textContent='Champ(s) invalide(s) : '+errs.join(', ');err.style.display='block';return;}
        err.style.display='none';
        var btn=q('mc-submit');btn.disabled=true;btn.textContent='Envoi...';
        addMsg(p+' '+n+' - '+e,'user');showTyping();
        post('action=form&conversation_id='+mcId+'&data='+encodeURIComponent(JSON.stringify({prenom:p,nom:n,email:e,telephone:t})),function(d){
            hideTyping();btn.disabled=false;btn.textContent='Recevoir mon devis pour '+modelName;
            if(d.error){err.textContent=d.error;err.style.display='block';return;}
            if(d.message)addMsg(d.message,'bot');
            hideForm();hideInput();if(d.options)showBtns(d.options);
        });
    }

    function addMsg(text,type){
        if(!text)return;var c=q('mc-msgs'),d=document.createElement('div');
        d.style.cssText='margin:8px 0;padding:12px 16px;border-radius:16px;max-width:88%;font-size:13px;line-height:1.6;word-wrap:break-word;animation:mcfade .3s ease;'+(type==='bot'?'background:#fff;color:#333;margin-right:auto;border:1px solid #e8e8e8;border-bottom-left-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,0.05);':'background:#1a5653;color:#fff;margin-left:auto;border-bottom-right-radius:4px;max-width:75%;');
        var s=text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        d.innerHTML=s.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>').replace(/\n/g,'<br>');
        c.appendChild(d);c.scrollTop=c.scrollHeight;
    }
    function showBtns(opts){
        if(!opts||!opts.length)return;var c=q('mc-msgs'),w=document.createElement('div');w.className='mc-opts';
        w.style.cssText='margin:8px 0;display:flex;flex-direction:column;gap:5px;';
        opts.forEach(function(o){
            var b=document.createElement('button');b.textContent=o.label;
            b.style.cssText='display:block;width:100%;padding:10px 14px;background:#fff;border:1.5px solid #1a5653;border-radius:10px;cursor:pointer;text-align:left;font-size:13px;color:#1a5653;font-weight:500;transition:all .15s;';
            b.onmouseenter=function(){this.style.background='#1a5653';this.style.color='#fff';};
            b.onmouseleave=function(){this.style.background='#fff';this.style.color='#1a5653';};
            b.onclick=function(){
                if(o.action==='close')return;
                if(o.action==='link'&&o.url){window.location.href=o.url;return;}
                send(o.value,o.label);
            };
            w.appendChild(b);
        });
        c.appendChild(w);c.scrollTop=c.scrollHeight;
    }
    function clearBtns(){var a=document.querySelectorAll('.mc-opts');for(var i=0;i<a.length;i++)a[i].style.display='none';}
    function showForm(){q('mc-form').style.display='block';q('mc-inp').style.display='none';q('mf-p').focus();}
    function hideForm(){q('mc-form').style.display='none';q('mc-inp').style.display='flex';}
    function hideInput(){q('mc-inp').style.display='none';q('mc-form').style.display='none';}
    function showTyping(){
        if(q('mc-typ'))return;var c=q('mc-msgs'),d=document.createElement('div');d.id='mc-typ';
        d.style.cssText='margin:8px 0;padding:12px 16px;background:#fff;border-radius:16px;border-bottom-left-radius:4px;display:inline-flex;gap:5px;border:1px solid #e8e8e8;';
        d.innerHTML='<span class="cb-dot"></span><span class="cb-dot"></span><span class="cb-dot"></span>';
        c.appendChild(d);c.scrollTop=c.scrollHeight;
    }
    function hideTyping(){var e=q('mc-typ');if(e)e.remove();}
    function post(body,cb){
        var x=new XMLHttpRequest();x.open('POST',apiUrl,true);
        x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');x.timeout=15000;
        x.onreadystatechange=function(){if(x.readyState===4){if(x.status===200){try{cb(JSON.parse(x.responseText));}catch(e){cb({error:'Erreur serveur'});}}else{cb({error:'Connexion impossible'});}}};
        x.ontimeout=function(){cb({error:'Délai dépassé'});};x.send(body);
    }
})();
</script>
<style>@keyframes mcfade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}</style>

<!-- Ancien formulaire masqué (fallback) -->
<section class="section" style="display:none;" id="devis-form-fallback">
    <div class="container container-narrow">
        <div style="background: var(--color-white); padding: var(--space-8); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
            <form action="<?php echo url('contact-process.php'); ?>" method="POST" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="modele_interesse" value="<?php echo $modele['id']; ?>">
                <input type="hidden" name="type_demande" value="devis">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Civilité</label>
                        <select name="civilite" class="form-control">
                            <option value="M">Monsieur</option>
                            <option value="Mme">Madame</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom <span class="required">*</span></label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone <span class="required">*</span></label>
                        <input type="tel" name="telephone" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Code postal</label>
                        <input type="text" name="code_postal" class="form-control" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Avez-vous déjà un terrain ?</label>
                    <select name="terrain_prevu" class="form-control">
                        <option value="0">Non, je recherche</option>
                        <option value="1">Oui, j'ai un terrain</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Délai souhaité</label>
                    <select name="delai_souhaite" class="form-control">
                        <option value="immediatement">Immédiatement</option>
                        <option value="3-mois">Dans 3 mois</option>
                        <option value="6-mois" selected>Dans 6 mois</option>
                        <option value="1-an">Dans 1 an</option>
                        <option value="plus">Plus tard</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Message (optionnel)</label>
                    <textarea name="commentaire" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-check" style="margin-bottom: var(--space-6);">
                    <input type="checkbox" id="consent" name="consent" required>
                    <label for="consent">J'accepte que mes données soient utilisées pour me recontacter. <a href="<?php echo url('politique-confidentialite.php'); ?>" target="_blank">En savoir plus</a>.</label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Envoyer ma demande</button>
            </form>
        </div>
    </div>
</section>

<?php 
// Préparer les données pour la lightbox
$galerie_data = json_encode(array_map(function($img) use ($modele) {
    return [
        'src' => url('uploads/maisons/' . $img),
        'alt' => clean($modele['nom'])
    ];
}, $all_images));
?>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" onclick="closeLightbox(event)">
    <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
    <button class="lightbox-nav lightbox-prev" onclick="navigateLightbox(-1)">&#10094;</button>
    <button class="lightbox-nav lightbox-next" onclick="navigateLightbox(1)">&#10095;</button>
    <div class="lightbox-content">
        <img id="lightbox-img" src="" alt="">
        <div class="lightbox-counter">
            <span id="lightbox-current">1</span> / <span id="lightbox-total"><?php echo count($all_images); ?></span>
        </div>
    </div>
</div>

<style>
/* Zoom hint */
.modele-image-main {
    position: relative;
    cursor: zoom-in;
}
.zoom-hint {
    position: absolute;
    bottom: var(--space-4);
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.7);
    color: white;
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius);
    font-size: var(--text-sm);
    opacity: 0;
    transition: opacity 0.3s;
}
.modele-image-main:hover .zoom-hint {
    opacity: 1;
}

/* Lightbox */
.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.95);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.lightbox.active {
    display: flex;
}
.lightbox-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}
.lightbox-content img {
    max-width: 100%;
    max-height: 85vh;
    object-fit: contain;
    border-radius: var(--radius);
}
.lightbox-close {
    position: absolute;
    top: var(--space-4);
    right: var(--space-4);
    background: rgba(255,255,255,0.2);
    color: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    font-size: 30px;
    cursor: pointer;
    transition: background 0.3s;
    z-index: 10000;
}
.lightbox-close:hover {
    background: rgba(255,255,255,0.4);
}
.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.2);
    color: white;
    border: none;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    transition: background 0.3s;
    z-index: 10000;
}
.lightbox-nav:hover {
    background: rgba(255,255,255,0.4);
}
.lightbox-prev { left: var(--space-4); }
.lightbox-next { right: var(--space-4); }
.lightbox-counter {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    font-size: var(--text-lg);
}
@media (max-width: 768px) {
    .lightbox-nav {
        width: 45px;
        height: 45px;
        font-size: 18px;
    }
    .lightbox-prev { left: var(--space-2); }
    .lightbox-next { right: var(--space-2); }
}
</style>

<script>
const galleryImages = <?php echo $galerie_data; ?>;
let currentLightboxIndex = 0;

function changeImage(index) {
    currentLightboxIndex = index;
    const mainImg = document.getElementById('main-image');
    const thumbs = document.querySelectorAll('.modele-thumb');
    
    mainImg.src = galleryImages[index].src;
    
    thumbs.forEach((thumb, i) => {
        thumb.classList.toggle('active', i === index);
    });
}

function openLightbox(index) {
    currentLightboxIndex = index;
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    
    img.src = galleryImages[index].src;
    img.alt = galleryImages[index].alt;
    document.getElementById('lightbox-current').textContent = index + 1;
    
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox(event) {
    if (event && event.target !== event.currentTarget) return;
    
    const lightbox = document.getElementById('lightbox');
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
}

function navigateLightbox(direction) {
    currentLightboxIndex += direction;
    
    if (currentLightboxIndex < 0) {
        currentLightboxIndex = galleryImages.length - 1;
    } else if (currentLightboxIndex >= galleryImages.length) {
        currentLightboxIndex = 0;
    }
    
    const img = document.getElementById('lightbox-img');
    img.src = galleryImages[currentLightboxIndex].src;
    img.alt = galleryImages[currentLightboxIndex].alt;
    document.getElementById('lightbox-current').textContent = currentLightboxIndex + 1;
}

// Navigation clavier
document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('lightbox');
    if (!lightbox.classList.contains('active')) return;
    
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') navigateLightbox(-1);
    if (e.key === 'ArrowRight') navigateLightbox(1);
});
</script>

<?php include 'includes/footer.php'; ?>
