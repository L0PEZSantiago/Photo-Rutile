# Le Sitemap XML

Ce document explique **ce qu'est un sitemap**, **pourquoi on en a créé un**, et **comment il fonctionne techniquement** dans le projet PhotosRutile. Il s'adresse à quelqu'un qui n'a aucune connaissance préalable du sujet.

---

## 1. C'est quoi un sitemap, et pourquoi en avoir un ?

### Le problème : comment Google trouve-t-il les pages de votre site ?

Quand Google (ou Bing, ou n'importe quel moteur de recherche) veut référencer votre site, il envoie un robot (appelé « crawler » ou « spider ») qui visite votre site. Ce robot part de votre page d'accueil, lit les liens (`<a href="...">`) présents sur la page, puis visite chaque lien, et ainsi de suite.

C'est un peu comme si vous lisiez un livre en suivant uniquement les renvois en bas de page.

**Le problème :** si un lien est difficile à trouver ou isolé, le robot risque de mettre du temps à le découvrir ou de le manquer.

### La solution : le sitemap

Un **sitemap** (littéralement « carte du site ») est un fichier spécial, au format XML, mis à disposition des moteurs de recherche à une adresse connue : `/sitemap.xml`.

Ce fichier contient la **liste claire de toutes les pages publiques importantes** de votre site, avec des indications pour chaque page :

- `<loc>` — l'URL complète et absolue de la page (ex: `https://photorutile.fr/creations`)
- `<lastmod>` — la date de dernière modification (optionnelle)
- `<changefreq>` — à quelle fréquence le contenu est susceptible de changer (`weekly`, `monthly`, etc.)
- `<priority>` — l'importance relative de la page par rapport aux autres (de `0.0` à `1.0`)

En déclarant ensuite `Sitemap: https://photorutile.fr/sitemap.xml` dans votre fichier `public/robots.txt`, vous indiquez à Google où trouver ce fichier dès sa première visite.

---

## 2. Les fichiers concernés

| Rôle | Fichier |
|------|---------|
| Contrôleur : génère les URLs et renvoie la réponse XML | `src/Controller/SitemapController.php` |
| Template : transforme le tableau d'URLs en XML conforme au standard | `templates/sitemap/sitemap.xml.twig` |
| Fichier robots : indique l'emplacement du sitemap aux moteurs | `public/robots.txt` |

---

## 3. Le flux de données, de la requête à la réponse

Voici ce qui se passe **dans l'ordre**, depuis le moment où Google visite `https://photorutile.fr/sitemap.xml` :

```
Google (ou un navigateur)
        |
        | Requête GET /sitemap.xml
        v
   Symfony (le routeur)
        |
        | La route 'app.sitemap' correspond à /sitemap.xml
        | -> Symfony appelle SitemapController::index()
        v
   SitemapController
        |
        | 1. Construit la liste des URLs absolues des pages publiques
        |    (Accueil, Créations, Contact, Localisation)
        | 2. Envoie la liste au template Twig
        v
   Template Twig (sitemap.xml.twig)
        |
        | Génère le XML valide en bouclant sur la liste d'URLs
        v
   Réponse HTTP
        |
        | Headers : Content-Type: application/xml + Cache HTTP 24h
        | Corps : le XML généré
        v
   Google reçoit le sitemap et indexe les pages du site
```

---

## 4. Explication du contrôleur — `SitemapController.php`

### Les imports et la classe

```php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
```

- **`UrlGeneratorInterface`** : permet de générer des URLs **absolues** (ex. `https://photorutile.fr/contact`) à partir du nom des routes Symfony. C'est indispensable car les moteurs de recherche exigent des URLs complètes avec protocole et nom de domaine.
- **`AbstractController`** : la classe de base Symfony qui fournit `$this->generateUrl()` et `$this->renderView()`.

### La route

```php
#[Route('/sitemap.xml', name: 'app.sitemap', defaults: ['_format' => 'xml'])]
public function index(): Response
```

- Quand un client demande `/sitemap.xml`, Symfony exécute cette méthode.
- `_format => 'xml'` documente que cette route produit du XML.

### La définition des pages indexables

```php
$urls = [
    [
        'loc'        => $this->generateUrl('app.home', [], UrlGeneratorInterface::ABSOLUTE_URL),
        'lastmod'    => null,
        'changefreq' => 'weekly',
        'priority'   => '1.0',
    ],
    [
        'loc'        => $this->generateUrl('app.creation.index', [], UrlGeneratorInterface::ABSOLUTE_URL),
        'lastmod'    => null,
        'changefreq' => 'weekly',
        'priority'   => '0.9',
    ],
    [
        'loc'        => $this->generateUrl('app.contact', [], UrlGeneratorInterface::ABSOLUTE_URL),
        'lastmod'    => null,
        'changefreq' => 'monthly',
        'priority'   => '0.7',
    ],
    [
        'loc'        => $this->generateUrl('app.localisation', [], UrlGeneratorInterface::ABSOLUTE_URL),
        'lastmod'    => null,
        'changefreq' => 'monthly',
        'priority'   => '0.6',
    ],
];
```

Chaque page publique possède une priorité adaptée à son importance :
- **Accueil** (`1.0`) : porte d'entrée principale du site.
- **Créations** (`0.9`) : catalogue des pièces artisanales.
- **Contact** (`0.7`) : formulaire pour commander ou échanger.
- **Localisation** (`0.6`) : informations pour trouver l'atelier / les marchés.

### La création de la réponse et le cache

```php
$response = new Response(
    $this->renderView('sitemap/sitemap.xml.twig', ['urls' => $urls]),
    Response::HTTP_OK,
    ['Content-Type' => 'application/xml']
);

$response->setPublic();
$response->setMaxAge(86400);

return $response;
```

- **`Content-Type: application/xml`** : indique explicitement au client que le contenu renvoyé est du XML (et non du HTML).
- **`setMaxAge(86400)`** : active une mise en cache HTTP de 24 heures. Si plusieurs moteurs de recherche visitent le sitemap le même jour, le serveur peut renvoyer la réponse sans refaire le calcul.

---

## 5. Explication du template — `sitemap.xml.twig`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {% for url in urls %}
    <url>
        <loc>{{ url.loc }}</loc>
        {% if url.lastmod %}
        <lastmod>{{ url.lastmod }}</lastmod>
        {% endif %}
        <changefreq>{{ url.changefreq }}</changefreq>
        <priority>{{ url.priority }}</priority>
    </url>
    {% endfor %}
</urlset>
```

- **`<urlset xmlns="...">`** : balise standard exigée par le protocole Sitemaps.
- **`{% for url in urls %}`** : parcourt chaque URL déclarée dans le contrôleur.
- **`{% if url.lastmod %}`** : n'affiche la balise `<lastmod>` que si une date est renseignée (évite les balises vides).

---

## 6. Déclaration du Sitemap aux moteurs de recherche

1. **Dans `public/robots.txt`** :
   ```
   Sitemap: https://photorutile.fr/sitemap.xml
   ```
2. **Dans Google Search Console** :
   - Rendez-vous dans la section **Sitemaps**
   - Entrez `sitemap.xml` et validez.
