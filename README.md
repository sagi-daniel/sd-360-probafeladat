# 360 Marketing – WordPress próbafeladat

Készítette: **Sági Dániel** · Verzió: **1.0.0**

A megoldás egy önálló WordPress bővítmény. Bármelyik témával működik, és nem módosítja a téma fájljait.

## Követelmények

| | Minimum |
|---|---|
| PHP | **8.3** |
| WordPress | **6.5** |

Ha a szerver nem felel meg a követelményeknek, a bővítmény nem aktiválható. Az aktiválás hibaüzenettel leáll, és kiírja, melyik verzió hiányzik. Ha a bővítmény már aktív, és a környezet később régebbi verzióra vált, az admin felületen hibaüzenet jelenik meg, a bővítmény pedig nem töltődik be.

## Telepítés

1. WordPress admin → **Bővítmények → Új hozzáadása → Bővítmény feltöltése**, majd töltsd fel az `sd-360-probafeladat.zip` fájlt.
2. Aktiváld a **„360 Marketing próbafeladat – Blog CPT és városválasztó”** bővítményt. Aktiváláskor a permalinkek automatikusan frissülnek.

## 1. rész – Blog post type, lista, frontend törlés

1. Az admin menüben megjelenik a **Blog** menüpont (post type: `blog`). Itt hozz létre 2 bejegyzést, tetszőleges címmel.
   - Gyorsabb megoldás: **Blog → Teszt bejegyzések → „Teszt bejegyzések hozzáadása”**. A gomb 2 egyszerű teszt blog bejegyzést hoz létre az aktuális oldalon. Csak kattintásra fut le, aktiváláskor nem.
2. Hozz létre egy oldalt (pl. „Blog lista”), és tedd bele ezt a shortcode-ot:

   ```
   [sd_blog_lista]
   ```

3. Az oldalon a blog bejegyzések listája jelenik meg **címmel, dátummal és Törlés gombbal**.
4. A Törlés gombra kattintva megerősítő ablak (`confirm`) jelenik meg. „OK” után a bejegyzés törlődik, az **adminisztrátor pedig e-mailt kap** róla (címzett: Beállítások → Általános → Adminisztrációs e-mail cím).

Megjegyzések:

- A lekérdezés a feladat kérésének megfelelően `query_posts()`-szal készült, utána `wp_reset_query()` állítja vissza a fő lekérdezést. Éles projektben `WP_Query`-t használnék, mert a `query_posts()` felülírja a fő lekérdezést.
- A Törlés gomb csak annak jelenik meg, akinek joga van a bejegyzést törölni. A kérést nonce, jogosultság-ellenőrzés és post type ellenőrzés védi.
- A törlés a WordPress saját működéséhez hasonlóan a **kukába** helyezi a bejegyzést, így az admin felületen visszaállítható.
- Helyi környezetben az e-mail küldéshez SMTP vagy levélelfogó kell (pl. Mailpit, WP Mail SMTP).

## 2. rész – Városválasztó JavaScript nélkül

1. Tedd ugyanerre vagy egy másik oldalra:

   ```
   [sd_varos_form]
   ```

2. A legördülő listában Debrecen, Budapest és Sopron közül lehet választani. Az alapértelmezett érték **Budapest**.
3. A Mentés gomb sima HTML `POST` űrlapot küld, **JavaScript nincs**. Az érték a `wp_options` táblába kerül (`sd_varos`). Mentés után átirányítás következik (Post/Redirect/Get), így a frissítés nem küldi újra az űrlapot, a mentett város pedig kiválasztva marad.
4. A mentéshez adminisztrátori jogosultság kell. A szerver csak a három megengedett értéket fogadja el (whitelist).

## Felépítés

```
sd-360-probafeladat/
├── sd-360-probafeladat.php          Bővítmény fejléc, verzió guard, betöltés, aktiválás
├── uninstall.php                    Törléskor eltávolítja az sd_varos opciót
└── includes/
    ├── class-blog.php               Blog: post type, [sd_blog_lista], törlés, admin e-mail
    ├── class-city-form.php          City_Form: [sd_varos_form], mentés
    └── class-sample-content.php     Sample_Content: Blog → Teszt bejegyzések admin oldal
```

- Az osztályok a `SagiDaniel\Probafeladat` névtérben vannak, így nem ütköznek más bővítmények vagy témák osztályaival.
- Minden feladatrész egy saját osztály. Az űrlapokat az `admin-post.php` dolgozza fel, mentés vagy törlés után a felhasználó visszakerül az előző oldalra.
- A bővítmény eltávolításkor a blog bejegyzéseket nem törli, mert azok felhasználói tartalmak.
