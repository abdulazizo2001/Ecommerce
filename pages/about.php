<?php
/**
 * Page "Qui sommes-nous"
 * Page statique présentant l'équipe et le concept du site
 */

require_once __DIR__ . '/../config/database.php';

$page_title = 'Qui sommes-nous';

include __DIR__ . '/../includes/header.php';
?>

<div style="background: white; padding: 3rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2 style="text-align: center; color: #333; margin-bottom: 2rem;">À propos de Fashion Shop</h2>
    
    <div style="max-width: 800px; margin: 0 auto;">
        <section style="margin-bottom: 3rem;">
            <h3 style="color: #667eea; margin-bottom: 1rem;">Notre Histoire</h3>
            <p style="color: #666; line-height: 1.8; text-align: justify;">
                Fashion Shop est né de la passion de deux étudiants en développement web qui souhaitaient créer 
                une plateforme e-commerce moderne et accessible. Notre objectif est de proposer des vêtements et 
                accessoires de qualité, tendance et abordables pour tous.
            </p>
            <p style="color: #666; line-height: 1.8; text-align: justify;">
                Nous croyons que la mode doit être accessible à tous, sans compromettre la qualité ni le style. 
                C'est pourquoi nous sélectionnons soigneusement chaque produit de notre catalogue pour vous 
                garantir satisfaction et confort.
            </p>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h3 style="color: #667eea; margin-bottom: 1rem;">Notre Mission</h3>
            <ul style="color: #666; line-height: 2; margin-left: 2rem;">
                <li>Offrir des produits de qualité à des prix compétitifs</li>
                <li>Garantir une expérience d'achat fluide et sécurisée</li>
                <li>Proposer un service client réactif et à l'écoute</li>
                <li>Suivre les dernières tendances de la mode</li>
                <li>Respecter l'environnement à travers nos choix de partenaires</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h3 style="color: #667eea; margin-bottom: 1rem;">Nos Valeurs</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 1.5rem;">
                <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 10px;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">✨</div>
                    <h4 style="color: #333; margin-bottom: 0.5rem;">Qualité</h4>
                    <p style="color: #666; font-size: 0.9rem;">Des produits soigneusement sélectionnés</p>
                </div>
                <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 10px;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🤝</div>
                    <h4 style="color: #333; margin-bottom: 0.5rem;">Confiance</h4>
                    <p style="color: #666; font-size: 0.9rem;">Transparence et honnêteté</p>
                </div>
                <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 10px;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🌍</div>
                    <h4 style="color: #333; margin-bottom: 0.5rem;">Responsabilité</h4>
                    <p style="color: #666; font-size: 0.9rem;">Respect de l'environnement</p>
                </div>
            </div>
        </section>
        
        <section style="margin-bottom: 3rem;">
            <h3 style="color: #667eea; margin-bottom: 1rem;">L'Équipe</h3>
            <p style="color: #666; line-height: 1.8; text-align: justify;">
                Notre équipe est composée de passionnés de mode et de technologie. Nous travaillons chaque jour 
                pour améliorer votre expérience shopping et vous proposer les meilleures tendances du moment.
            </p>
            <p style="color: #666; line-height: 1.8; text-align: justify;">
                Ce projet a été développé dans le cadre de notre formation en développement web, mais notre 
                engagement envers la qualité et la satisfaction client est bien réel !
            </p>
        </section>
        
        <section style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem; border-radius: 10px; text-align: center;">
            <h3 style="color: white; margin-bottom: 1rem;">Une Question ?</h3>
            <p style="color: white; margin-bottom: 1.5rem;">Notre équipe est là pour vous aider !</p>
            <a href="<?php echo url('pages/articles.php'); ?>" class="btn" style="background: white; color: #667eea;">
                Découvrir nos produits
            </a>
        </section>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>