-- Etape 1: remplissage de la table `elected_representative`
-- création d'un champ temporaire `canonical` et sa clé unique
ALTER TABLE elected_representative ADD canonical VARCHAR(255) DEFAULT NULL
;
CREATE UNIQUE INDEX elected_representative_canonical ON elected_representative(canonical)
;
ALTER TABLE elected_representatives_register
    ADD canonical_adherent VARCHAR(255) DEFAULT NULL,
    ADD canonical_mandate VARCHAR(255) DEFAULT NULL,
    ADD INDEX err_canonical_adherent (canonical_adherent),
    ADD INDEX err_canonical_mandate (canonical_mandate)
;
-- remplir les champs `canonical`s de `elected_representatives_register`
UPDATE elected_representatives_register SET canonical_adherent = CONCAT_WS('-',prenom, nom, DATE_FORMAT(date_naissance, '%Y-%m-%d'))
;
UPDATE elected_representatives_register SET canonical_mandate = CONCAT_WS('-', prenom, nom, DATE_FORMAT(date_naissance, '%Y-%m-%d'), type_elu, nuance_politique, date_debut_mandat)
;
-- reprise des données vers la table `elected_representative`
INSERT IGNORE INTO elected_representative(last_name, first_name, gender, birth_date, official_id, canonical)
SELECT
    nom, prenom, genre, DATE_FORMAT(date_naissance, '%Y-%m-%d'), identification_elu, canonical_adherent
FROM
    elected_representatives_register
;
-- Etape 2: remplissage de la colonne `elected_representative.adherent_id`
-- creation d'une table temporaire listant tous les adhérents
CREATE TABLE temp_adherents_canonical(
    adherent_id INT(10) DEFAULT NULL,
    canonical VARCHAR(255) DEFAULT NULL,
    INDEX temp_adherents_canonical_canonical (canonical)
)
;
-- on insert tous les adherents avec leur canonical dans la table temporaire
INSERT INTO temp_adherents_canonical (
    adherent_id,
    canonical
)
SELECT
    a.id,
    CONCAT_WS(
            '-',
            a.first_name,
            a.last_name,
            a.birthdate
        )
FROM adherents a
;
-- On met à jour le `adherent_id` des élus
UPDATE elected_representative AS elu
    INNER JOIN temp_adherents_canonical AS tmp
    ON tmp.canonical = elu.canonical
SET elu.adherent_id = tmp.adherent_id,
    elu.is_adherent = NULL
;
-- on vérifie que `adherent_id` est bien maj
SELECT COUNT(id) FROM elected_representative WHERE adherent_id IS NOT NULL
;
-- Etape 3: remplir les mandats
-- Etape 3a: ajouter les champs supplémantaires
ALTER TABLE elected_representative_mandate
    ADD dpt VARCHAR(5) DEFAULT NULL,
    ADD dpt_nom VARCHAR(255) DEFAULT NULL,
    ADD epci_nom VARCHAR(255) DEFAULT NULL,
    ADD commune_nom VARCHAR(255) DEFAULT NULL,
    ADD region_nom VARCHAR(255) DEFAULT NULL,
    ADD circo_legis_nom VARCHAR(255) DEFAULT NULL,
    ADD circo_legis_code INT(20) DEFAULT NULL,
    ADD canonical VARCHAR(255) NOT NULL,
    ADD epci VARCHAR(255) DEFAULT NULL,
    ADD ville VARCHAR(255) DEFAULT NULL,
    ADD UNIQUE INDEX elected_representative_mandate_canonical (canonical),
    ADD INDEX elected_representative_epci (epci),
    ADD INDEX elected_representative_ville (ville)
;
-- Etape 3b: remplissage de la table `elected_representative_mandate`
UPDATE elected_representatives_register SET date_debut_mandat = '2014-03-30'
WHERE (date_debut_mandat = '' OR date_debut_mandat IS NULL) AND type_elu = 'membre_EPCI'
;
INSERT IGNORE INTO elected_representative_mandate (
    elected_representative_id,
    type,
    is_elected,
    begin_at,
    political_affiliation,
    on_going,
    number,
    dpt,
    dpt_nom,
    epci_nom,
    commune_nom,
    region_nom,
    circo_legis_nom,
    circo_legis_code,
    canonical,
    ville,
    epci
)
SELECT
    er.id,
    type_elu,
    1,
    date_debut_mandat,
    nuance_politique,
    1,
    1,
    dpt,
    dpt_nom,
    epci_nom,
    commune_nom,
    region_nom,
    circo_legis_nom,
    circo_legis_code,
    canonical_mandate,
    CONCAT(commune_nom, ' (', dpt, '%'),
    REPLACE(REPLACE(REPLACE(epci_nom, '\'', ' '), '-', ' '), '  ', ' ')
FROM elected_representatives_register err
    LEFT JOIN elected_representative er ON er.canonical = err.canonical_adherent
;
-- Etape 3c: ajouter les zones par mandat type
-- conseiller_municipal
ALTER TABLE elected_representative_mandate
    ADD INDEX er_mandate_type (type)
;
ALTER TABLE elected_representative_zone
    ADD INDEX er_zone_name (name)
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 1er (75001)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Jean-François-LEGARET-1952-08-21-conseiller_municipal-LR-2014-03-23'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 2eme (75002)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Jacques-BOUTAULT-1961-01-04-conseiller_municipal-LVEC-2014-03-30',
        'Véronique-LEVIEUX-1971-05-30-conseiller_municipal-LUG-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 3eme (75003)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Pierre-AIDENBAUM-1942-05-03-conseiller_municipal-LUG-2014-03-30',
        'Laurence-GOLDGRAB-1959-10-27-conseiller_municipal-NC-2014-03-30',
        'Marie-Laure-HAREL-1984-03-24-conseiller_municipal-LUD-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 4eme (75004)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Christophe-GIRARD-1956-02-09-conseiller_municipal-NC-2014-03-30',
        'Karen-TAIEB ATTIAS-1962-11-24-conseiller_municipal-NC-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 5eme (75005)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Florence-BERTHOUT-1952-06-25-conseiller_municipal-LUD-2014-03-30',
        'Marie-Christine-LEMARDELEY-1953-02-03-conseiller_municipal-LUG-2014-03-30',
        'Dominique-STOPPA-LYONNET-1956-06-22-conseiller_municipal-NC-2014-03-30',
        'Dominique-TIBERI-1959-10-08-conseiller_municipal-LDVD-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 6eme (75006)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Marielle-DE SARNEZ-1951-03-27-conseiller_municipal-NC-2014-03-23',
        'Jean-Pierre-LECOQ-1954-07-18-conseiller_municipal-DVD-2014-03-23',
        'Alexandre-VESPERINI-1987-06-16-conseiller_municipal-NC-2014-03-23'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 7eme (75007)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Rachida-DATI-1965-11-27-conseiller_municipal-LUD-2014-03-30',
        'Emmanuelle-DAUVERGNE-1971-10-03-conseiller_municipal-NC-2014-03-30',
        'Thierry-HODENT-1954-11-22-conseiller_municipal-NC-2014-03-30',
        'Yves-POZZO DI BORGO-1948-05-03-conseiller_municipal-NC-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 8eme (75008)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Jeanne-D''HAUTESERRE-1953-07-28-conseiller_municipal-LUD-2014-03-30',
        'Catherine-LECUYER-1973-02-01-conseiller_municipal-NC-2014-03-30',
        'Pierre-LELLOUCHE-1951-05-03-conseiller_municipal-NC-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 9eme (75009)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Gypsie-BLOCH-1972-04-30-conseiller_municipal-NC-2014-03-30',
        'Delphine-BURKLI-1974-06-05-conseiller_municipal-LUD-2014-03-30',
        'Jean-Baptiste-DE FROMENT-1977-10-07-conseiller_municipal-NC-2014-03-30',
        'Pauline-VERON-1974-04-04-conseiller_municipal-SOC-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 10eme (75010)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Yamina-BENGUIGUI-1955-04-09-conseiller_municipal-NC-2014-03-30',
        'Alexandra-CORDEBARD-1967-01-01-conseiller_municipal-NC-2014-03-30',
        'Rémi-FERAUD-1971-08-24-conseiller_municipal-LUG-2014-03-30',
        'Bernard-GAUDILLERE-1950-02-06-conseiller_municipal-NC-2014-03-30',
        'Didier-LE RESTE-1955-06-02-conseiller_municipal-COM-2014-03-30',
        'Déborah-PAWLIK-1980-11-19-conseiller_municipal-LR-2014-03-30',
        'Anne-SOUYRIS-1964-08-29-conseiller_municipal-LVEC-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 11eme (75011)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'David-BELLIARD-1978-05-29-conseiller_municipal-NC-2014-03-30',
        'Hélène-BIDARD-1981-04-06-conseiller_municipal-NC-2014-03-30',
        'Patrick-BLOCHE-1956-07-04-conseiller_municipal-NC-2014-03-30',
        'Leïla-DIRI-1982-12-28-conseiller_municipal-NC-2014-03-30',
        'Philippe-DUCLOUX-1961-10-30-conseiller_municipal-NC-2014-03-30',
        'Jean-François-MARTINS-1981-12-10-conseiller_municipal-NC-2014-03-30',
        'Joëlle-MOREL-1955-10-04-conseiller_municipal-NC-2014-03-30',
        'Nawel-OUMER-1973-09-02-conseiller_municipal-DVG-2014-03-30',
        'Christian-SAINT-ETIENNE-1951-10-15-conseiller_municipal-LUD-2014-03-30',
        'François-VAUGLIN-1969-12-22-conseiller_municipal-LUG-2014-03-30',
        'Mercedes-ZUNIGA-1949-11-06-conseiller_municipal-NC-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 12eme (75012)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Catherine-BARATTI-ELBAZ-1969-07-14-conseiller_municipal-LUG-2014-03-30',
        'Nicolas-BONNET OULALDJ-1974-05-18-conseiller_municipal-NC-2014-03-30',
        'Sandrine-CHARNOZ-1972-01-09-conseiller_municipal-NC-2014-03-30',
        'Emmanuel-GREGOIRE-1977-12-24-conseiller_municipal-NC-2014-03-30',
        'François-HAAB-1964-04-08-conseiller_municipal-NC-2014-03-30',
        'Pénélope-KOMITES-1959-05-06-conseiller_municipal-NC-2014-03-30',
        'Jean-Louis-MISSIKA-1951-03-06-conseiller_municipal-NC-2014-03-30',
        'Valérie-MONTANDON-1976-03-31-conseiller_municipal-LR-2014-03-30',
        'Christophe-NAJDOVSKI-1969-08-09-conseiller_municipal-LVEC-2014-03-30',
        'Catherine-VIEU-CHARIER-1957-03-21-conseiller_municipal-NC-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 13eme (75013)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Jean-Noël-AQUA-1974-01-01-conseiller_municipal-COM-2014-03-30',
        'Marie-ATALLAH-1956-09-08-conseiller_municipal-NC-2014-03-30',
        'Emmanuelle-BECKER-1983-07-15-conseiller_municipal-NC-2014-03-30',
        'Yves-CONTASSOT-1950-04-26-conseiller_municipal-LVEC-2014-03-30',
        'Jérôme-COUMET-1967-01-22-conseiller_municipal-LUG-2014-03-30',
        'Marie-Pierre-DE LA GONTRIE-1958-12-18-conseiller_municipal-NC-2014-03-30',
        'Edith-GALLOIS-1959-01-09-conseiller_municipal-LUD-2014-03-30',
        'Bruno-JULLIARD-1981-02-09-conseiller_municipal-NC-2014-03-30',
        'Anne-Christine-LANG-1961-08-10-conseiller_municipal-REM-2014-03-30',
        'Jean-Marie-LE GUEN-1953-01-03-conseiller_municipal-NC-2014-03-30',
        'Annick-OLIVIER-1952-04-04-conseiller_municipal-NC-2014-03-30',
        'Buon-TAN-1967-03-10-conseiller_municipal-REM-2014-03-30',
        'Patrick-TREMEGE-1954-05-14-conseiller_municipal-NC-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 14eme (75014)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Eric-AZIERE-1955-07-28-conseiller_municipal-NC-2014-03-30',
        'Hervé-BEGUE-1956-01-12-conseiller_municipal-NC-2014-03-30',
        'Célia-BLAUEL-1981-11-10-conseiller_municipal-LVEC-2014-03-30',
        'Pascal-CHERKI-1966-09-01-conseiller_municipal-SOC-2014-03-30',
        'Nathalie-KOSCIUSKO-MORIZET-1973-05-14-conseiller_municipal-NC-2014-03-30',
        'Caroline-MECARY-1963-04-16-conseiller_municipal-NC-2014-03-30',
        'Etienne-MERCIER-1971-06-23-conseiller_municipal-NC-2014-03-30',
        'Carine-PETIT-1974-06-26-conseiller_municipal-NC-2014-03-30',
        'Olivia-POLSKI-1975-06-02-conseiller_municipal-NC-2014-03-30',
        'Hermano-SANCHES RUIVO-1966-05-23-conseiller_municipal-NC-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 15eme (75015)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Pascale-BLADIER-1966-05-12-conseiller_municipal-NC-2014-03-30',
        'Anne-Charlotte-BUFFETEAU-1984-07-18-conseiller_municipal-NC-2014-03-30',
        'Sylvie-CEYRAC-1949-09-19-conseiller_municipal-NC-2014-03-30',
        'Pierre-CHARON-1951-03-01-conseiller_municipal-NC-2014-03-30',
        'Daniel-Georges-COURTOIS-1956-08-29-conseiller_municipal-NC-2014-03-30',
        'François-David-CRAVENNE-1967-06-26-conseiller_municipal-NC-2014-03-30',
        'Claude-DARGENT-1957-09-23-conseiller_municipal-NC-2014-03-30',
        'Claire-DE CLERMONT-TONNERRE-1956-02-12-conseiller_municipal-NC-2014-03-30',
        'Agnès-EVREN-1970-12-27-conseiller_municipal-LR-2014-03-30',
        'Maud-GATEL-1979-04-06-conseiller_municipal-NC-2014-03-30',
        'Philippe-GOUJON-1954-04-30-conseiller_municipal-LR-2014-03-30',
        'Anne-HIDALGO-1959-06-19-conseiller_municipal-LUG-2014-03-30',
        'Jean-François-LAMOUR-1956-02-02-conseiller_municipal-LR-2014-03-30',
        'Franck-LEFEVRE-1964-09-28-conseiller_municipal-NC-2014-03-30',
        'Jean-Baptiste-MENGUY-1978-09-16-conseiller_municipal-NC-2014-03-30',
        'Anne-TACHENE-1967-01-11-conseiller_municipal-NC-2014-03-30',
        'Dominique-VERSINI-1954-07-17-conseiller_municipal-NC-2014-03-30',
        'Yann-WEHRLING-1971-07-03-conseiller_municipal-NC-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 16eme (75016)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Michèle-ASSOULINE-1974-10-30-conseiller_municipal-NC-2014-03-23',
        'Pierre-AURIACOMBE-1958-06-17-conseiller_municipal-NC-2014-03-23',
        'Julie-BOILLOT-1981-04-27-conseiller_municipal-NC-2014-03-23',
        'Céline-BOULAY-ESPERONNIER-1970-12-18-conseiller_municipal-NC-2014-03-23',
        'Stéphane-CAPLIEZ-1963-05-27-conseiller_municipal-NC-2014-03-23',
        'Grégoire-CHERTOK-1966-04-06-conseiller_municipal-NC-2014-03-23',
        'Pierre-GABORIAU-1951-12-16-conseiller_municipal-NC-2014-03-23',
        'Danièle-GIAZZI-1955-09-03-conseiller_municipal-NC-2014-03-23',
        'Claude-GOASGUEN-1945-03-12-conseiller_municipal-NC-2014-03-23',
        'Eric-HELARD-1961-10-16-conseiller_municipal-NC-2014-03-23',
        'Ann-Katrin-JEGO-1969-05-23-conseiller_municipal-NC-2014-03-23',
        'Thomas-LAURET-1971-07-08-conseiller_municipal-LUG-2014-03-23',
        'Béatrice-LECOUTURIER-1965-04-30-conseiller_municipal-NC-2014-03-23'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 17eme (75017)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Jean-Didier-BERTHAULT-1971-10-15-conseiller_municipal-NC-2014-03-23',
        'Alix-BOUGERET-1978-04-13-conseiller_municipal-NC-2014-03-23',
        'Geoffroy-BOULARD-1978-12-03-conseiller_municipal-NC-2014-03-23',
        'Bernard-DEBRE-1944-09-30-conseiller_municipal-NC-2014-03-23',
        'Jérôme-DUBUS-1962-03-05-conseiller_municipal-NC-2014-03-23',
        'Catherine-DUMAS-1957-07-13-conseiller_municipal-NC-2014-03-23',
        'Olga-JOHNSON-1964-11-06-conseiller_municipal-NC-2014-03-23',
        'Patrick-KLUGMAN-1977-07-11-conseiller_municipal-NC-2014-03-23',
        'Brigitte-KUSTER-1959-04-12-conseiller_municipal-LR-2014-03-23',
        'Annick-LEPETIT-1958-03-31-conseiller_municipal-NC-2014-03-23',
        'Valérie-NAHMIAS-1973-07-12-conseiller_municipal-UDI-2014-03-23',
        'Frédéric-PECHENARD-1957-03-12-conseiller_municipal-NC-2014-03-23'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 18eme (75018)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Pierre-Yves-BOURNAZEL-1977-08-31-conseiller_municipal-LR-2014-03-30',
        'Claudine-BOUYGUES-1959-06-20-conseiller_municipal-NC-2014-03-30',
        'Galla-BRIDIER-1981-05-11-conseiller_municipal-NC-2014-03-30',
        'Jean-Bernard-BROS-1955-11-06-conseiller_municipal-NC-2014-03-30',
        'Ian-BROSSAT-1980-04-23-conseiller_municipal-COM-2014-03-30',
        'Myriam-EL KHOMRI-1978-02-18-conseiller_municipal-SOC-2014-03-30',
        'Afaf-GABELOTAUD-1975-10-09-conseiller_municipal-NC-2014-03-30',
        'Didier-GUILLOT-1968-04-20-conseiller_municipal-NC-2014-03-30',
        'Christian-HONORE-1952-08-31-conseiller_municipal-NC-2014-03-30',
        'Pascal-JULIEN-1954-11-20-conseiller_municipal-LVEC-2014-03-30',
        'Eric-LEJOINDRE-1980-05-17-conseiller_municipal-LUG-2014-03-30',
        'Sandrine-MEES-1974-12-08-conseiller_municipal-NC-2014-03-30',
        'Fadila-MEHAL-1954-07-30-conseiller_municipal-NC-2014-03-30',
        'Danièle-PREMEL-1949-06-19-conseiller_municipal-NC-2014-03-30',
        'Daniel-VAILLANT-1949-07-19-conseiller_municipal-NC-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 19eme (75019)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'Colombe-BROSSEL-1976-04-19-conseiller_municipal-SOC-2014-03-30',
        'François-DAGNAUD-1962-01-04-conseiller_municipal-LUG-2014-03-30',
        'Léa-FILOCHE-1978-05-04-conseiller_municipal-NC-2014-03-30',
        'Fanny-GAILLANNE-1987-04-26-conseiller_municipal-NC-2014-03-30',
        'Jean-Jacques-GIANNESINI-1956-10-08-conseiller_municipal-LUD-2014-03-30',
        'Halima-JEMNI-1966-12-05-conseiller_municipal-NC-2014-03-30',
        'Bernard-JOMIER-1963-10-09-conseiller_municipal-LVEC-2014-03-30',
        'Fatoumata-KONE-1981-06-20-conseiller_municipal-NC-2014-03-30',
        'Roger-MADEC-1950-10-27-conseiller_municipal-NC-2014-03-30',
        'Nicolas-NORDMAN-1971-03-11-conseiller_municipal-NC-2014-03-30',
        'Anne-Constance-ONGHENA-1973-10-08-conseiller_municipal-LR-2014-03-30',
        'Mao-PENINOU-1968-03-20-conseiller_municipal-NC-2014-03-30',
        'Aurélie-SOLANS-1976-02-14-conseiller_municipal-NC-2014-03-30',
        'Sergio-TINTI-1961-05-21-conseiller_municipal-COM-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Paris 20eme (75020)' AND category_id = 1)
WHERE type = 'conseiller_municipal' AND canonical IN (
        'David-ASSOULINE-1959-06-16-conseiller_municipal-NC-2014-03-30',
        'Marinette-BACHE-1954-10-07-conseiller_municipal-NC-2014-03-30',
        'Julien-BARGETON-1973-03-29-conseiller_municipal-NC-2014-03-30',
        'Jacques-BAUDRIER-1966-06-11-conseiller_municipal-COM-2014-03-30',
        'Frédérique-CALANDRA-1962-11-15-conseiller_municipal-LUG-2014-03-30',
        'Virginie-DASPET-1971-11-09-conseiller_municipal-NC-2014-03-30',
        'Nathalie-FANFANT-1971-01-07-conseiller_municipal-NC-2014-03-30',
        'Jérôme-GLEIZES-1970-06-12-conseiller_municipal-NC-2014-03-30',
        'Antoinette-GUHL-1970-06-10-conseiller_municipal-ECO-2014-04-05',
        'Frédéric-HOCQUARD-1969-09-02-conseiller_municipal-NC-2014-03-30',
        'Nathalie-MAQUOI-1979-03-27-conseiller_municipal-NC-2014-03-30',
        'Atanase-PERIFAN-1964-08-19-conseiller_municipal-LR-2014-03-30',
        'Raphaëlle-PRIMET-1964-09-05-conseiller_municipal-NC-2014-03-30',
        'Danielle-SIMONNET-1971-07-02-conseiller_municipal-FI-2014-03-30'
    ) AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Amné (72540)' AND category_id = 1)
WHERE commune_nom = 'Amné-en-Champagne' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Auge-Saint-Médard (16170)' AND category_id = 1)
WHERE commune_nom = 'Auge St Medard' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Autrey (54160)' AND category_id = 1)
WHERE commune_nom = 'Autrey-sur-Madon' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Val-d''Épy (39160,39320)' AND category_id = 1)
WHERE commune_nom = 'Balme-d''Epy (La)' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bazouges Cré sur Loir (72200)' AND category_id = 1)
WHERE commune_nom = 'Bazouges-Cré-sur-Loir' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bellegarde (32140)' AND category_id = 1)
WHERE commune_nom = 'Bellegarde-Adoulins' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bergholtzzell (68500)' AND category_id = 1)
WHERE commune_nom = 'Bergholtz-zell' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Binic-Étables-sur-Mer (22520,22680)' AND category_id = 1)
WHERE commune_nom = 'Binic - Etables-sur-Mer' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bors (Canton de Charente-Sud) (16360)' AND category_id = 1)
WHERE commune_nom = 'Bors(Canton de Baignes-Sainte-Radegonde)' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bors (Canton de Tude-et-Lavalette) (16190)' AND category_id = 1)
WHERE commune_nom = 'Bors(Canton de Montmoreau-Saint-Cybard)' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Breuil (51140)' AND category_id = 1)
WHERE commune_nom = 'Breuil-sur-Vesle' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Champaubert (51270)' AND category_id = 1)
WHERE commune_nom = 'Champaubert-la-Bataille' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Champ-Laurent (73390)' AND category_id = 1)
WHERE commune_nom = 'Champlaurent' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chessy (69380)' AND category_id = 1)
WHERE commune_nom = 'Chessy-les-Mines' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Cléry-le-Grand (55110)' AND category_id = 1)
WHERE commune_nom = 'Cléry-Grand' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Cléry-le-Petit (55110)' AND category_id = 1)
WHERE commune_nom = 'Cléry-Petit' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Contault (51330)' AND category_id = 1)
WHERE commune_nom = 'Contault le Maupas' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Côteaux du Blanzacais (16250)' AND category_id = 1)
WHERE commune_nom = 'Côteaux du blancazais' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Cœur de Causse (46240)' AND category_id = 1)
WHERE commune_nom = 'Cur de Causse' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Douillet (72130)' AND category_id = 1)
WHERE commune_nom = 'Douillet-le-Joly' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Estampes (32170)' AND category_id = 1)
WHERE commune_nom = 'Estampes-Castelfranc' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fillé (72210)' AND category_id = 1)
WHERE commune_nom = 'Fillé-sur-Sarthe' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Gennes (25660)' AND category_id = 1)
WHERE commune_nom = 'Gennes-Val de Loire' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Gumond (19320)' AND category_id = 1)
WHERE commune_nom = 'Gumont' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Bâtie-des-Fonds (26310)' AND category_id = 1)
WHERE commune_nom = 'La Bâtie-des-Fonts' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Bretonnière-la-Claye (85320)' AND category_id = 1)
WHERE commune_nom = 'La Bretonnière-La Claye' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Chapelle du Lou du Lac (35360)' AND category_id = 1)
WHERE commune_nom = 'La Chapelle-du-Lou-du-Lac' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Mazière-aux-Bons-Hommes (23260)' AND category_id = 1)
WHERE commune_nom = 'La Mazière-aux-Bonshommes' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Salle-les-Alpes (05240)' AND category_id = 1)
WHERE commune_nom = 'La-Salle-les-Alpes' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Périgny (94520)' AND category_id = 1)
WHERE commune_nom = 'Périgny-sur-Yerres' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Reygade (19430)' AND category_id = 1)
WHERE commune_nom = 'Reygades' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Rimbachzell (68500)' AND category_id = 1)
WHERE commune_nom = 'Rimbach-zell' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Rix (39250)' AND category_id = 1)
WHERE commune_nom = 'Rix-Trebief' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Roinville (91410)' AND category_id = 1)
WHERE commune_nom = 'Roinville-sous-Dourdan' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Romilly (41270)' AND category_id = 1)
WHERE commune_nom = 'Romilly du Perche' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Rouez (72140)' AND category_id = 1)
WHERE commune_nom = 'Rouez-en-Champagne' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ruy-Montceau (38300)' AND category_id = 1)
WHERE commune_nom = 'Ruy' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint Bonnet-Laval (48600)' AND category_id = 1)
WHERE commune_nom = 'Saint Bonnet Laval' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Andéol (26150)' AND category_id = 1)
WHERE commune_nom = 'Saint-Andéol en Quint' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Christophe (73360)' AND category_id = 1)
WHERE commune_nom = 'Saint-Christophe-La-Grotte' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Christophe (69860)' AND category_id = 1)
WHERE commune_nom = 'Saint-Christophe-la-Montagne' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Léger (53480)' AND category_id = 1)
WHERE commune_nom = 'Saint-Léger-en-charnie' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Loube (32220)' AND category_id = 1)
WHERE commune_nom = 'Saint-Loube-Amades' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Loup (41320)' AND category_id = 1)
WHERE commune_nom = 'Saint-Loup sur Cher' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Loup-en-Champagne (08300)' AND category_id = 1)
WHERE commune_nom = 'Saint-Loup-Champagne' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint Martin de l''If (76190)' AND category_id = 1)
WHERE commune_nom = 'Saint-Martin-de-l''If' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Martin-Château (23460)' AND category_id = 1)
WHERE commune_nom = 'Saint-Martin-le-Château' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Paterne - Le Chevain (72610)' AND category_id = 1)
WHERE commune_nom = 'Saint-Paterne-Le Chevain' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Paul (73170)' AND category_id = 1)
WHERE commune_nom = 'Saint-Paul sur Yenne' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Servant (56120)' AND category_id = 1)
WHERE commune_nom = 'Saint-Servant-sur-Oust' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sarroux - Saint Julien (19110)' AND category_id = 1)
WHERE commune_nom = 'Sarroux-Saint Julien' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saulmory-Villefranche (55110)' AND category_id = 1)
WHERE commune_nom = 'Saulmory-et-Villefranche' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Septmoncel les Molunes (39310)' AND category_id = 1)
WHERE commune_nom = 'Septmoncel-Les-Molunes' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sossais (86230)' AND category_id = 1)
WHERE commune_nom = 'Sossay' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ville-Dommange (51390)' AND category_id = 1)
WHERE commune_nom = 'Villedommange' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Villefranche (32420)' AND category_id = 1)
WHERE commune_nom = 'Villefranche-d''Astarac' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Vorey (43800)' AND category_id = 1)
WHERE commune_nom = 'Vorey-sur-Arzon' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Argelès-Bagnères (65200)' AND category_id = 1)
WHERE commune_nom = 'Argelès' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Berville-en-Caux (76560)' AND category_id = 1)
WHERE commune_nom = 'Berville' AND dpt = '76' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Buxeuil (37160)' AND category_id = 1)
WHERE commune_nom = 'Buxeuil' AND dpt = '86' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Change (21340)' AND category_id = 1)
WHERE commune_nom = 'Change' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Claret (05110)' AND category_id = 1)
WHERE commune_nom = 'Claret' AND dpt = '04' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fresnes-sous-Coucy (02380)' AND category_id = 1)
WHERE commune_nom = 'Fresnes' AND dpt = '02' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Éloise (01200)' AND category_id = 1)
WHERE commune_nom = 'Eloise' AND dpt = '74' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Étaule (89200)' AND category_id = 1)
WHERE commune_nom = 'Etaules' AND dpt = '89' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Forges (17290)' AND category_id = 1)
WHERE commune_nom = 'Écouves' AND dpt = '17' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Forgès (19380)' AND category_id = 1)
WHERE commune_nom = 'Écouves' AND dpt = '19' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Forges (77130)' AND category_id = 1)
WHERE commune_nom = 'Écouves' AND dpt = '77' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Surville (27400)' AND category_id = 1)
WHERE commune_nom = 'La Haye' AND dpt = '27' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Baudreville (28310)' AND category_id = 1)
WHERE commune_nom = 'La Haye' AND dpt = '28' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Glatigny (60650)' AND category_id = 1)
WHERE commune_nom = 'La Haye' AND dpt = '60' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Surville (14130)' AND category_id = 1)
WHERE commune_nom = 'La Haye' AND dpt = '14' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Glatigny (57530)' AND category_id = 1)
WHERE commune_nom = 'La Haye' AND dpt = '57' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Rochette (06260)' AND category_id = 1)
WHERE commune_nom = 'La Rochette' AND dpt = '04' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lautenbachzell (68610)' AND category_id = 1)
WHERE commune_nom = 'Lautenbach-zell' AND dpt = '68' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Œting (57600)' AND category_id = 1)
WHERE commune_nom = 'OEting' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Œuf-en-Ternois (62130)' AND category_id = 1)
WHERE commune_nom = 'OEuf-en-Ternois' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Œuilly (51480)' AND category_id = 1)
WHERE commune_nom = 'OEuilly' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Œuilly (02160)' AND category_id = 1)
WHERE commune_nom = 'OEuilly' AND dpt = '02' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Bouchet-Mont-Charvin (74230)' AND category_id = 1)
WHERE commune_nom = 'Le Bouchet Mont-Charvin' AND dpt = '74' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Gault-du-Perche (41270)' AND category_id = 1)
WHERE commune_nom = 'Le Gault-Perche' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Mazet-Saint-Voy (43520)' AND category_id = 1)
WHERE commune_nom = 'Le Mazet-Saint-Voy' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Montfort (04600)' AND category_id = 1)
WHERE commune_nom = 'Le Val' AND dpt = '04' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Montfort (64190)' AND category_id = 1)
WHERE commune_nom = 'Le Val' AND dpt = '64' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Val-d''Ajol (88340)' AND category_id = 1)
WHERE commune_nom = 'Le Val d''Ajol' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Limours (91470)' AND category_id = 1)
WHERE commune_nom = 'Limours en Hurepoix' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'L''Isle-en-Rigault (55000)' AND category_id = 1)
WHERE commune_nom = 'Lisle-en-Rigault' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Loir en Vallée (72310,72340)' AND category_id = 1)
WHERE commune_nom = 'Loir-en-Vallée' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Maisonnay (79500)' AND category_id = 1)
WHERE commune_nom = 'Maisonnais' AND dpt = '79' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Marcellaz (74250)' AND category_id = 1)
WHERE commune_nom = 'Marcellaz-en-Faucigny' AND dpt = '74' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Mazaye (63230)' AND category_id = 1)
WHERE commune_nom = 'Mazayes' AND dpt = '63' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Montboudif (15190)' AND category_id = 1)
WHERE commune_nom = 'Mo1ntboudif' AND dpt = '15' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Monclar (32150)' AND category_id = 1)
WHERE commune_nom = 'Monclar-d''Armagnac' AND dpt = '32' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Norroy (88800)' AND category_id = 1)
WHERE commune_nom = 'Norroy-sur-Vair' AND dpt = '88' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Onesse-Laharie (40110)' AND category_id = 1)
WHERE commune_nom = 'Onesse-et-Laharie' AND dpt = '40' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Piégut (05130)' AND category_id = 1)
WHERE commune_nom = 'Piégut' AND dpt = '04' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lasson (89570)' AND category_id = 1)
WHERE commune_nom = 'Rots' AND dpt = '89' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Ouen-la-Thène (17490)' AND category_id = 1)
WHERE commune_nom = 'Saint-Ouen' AND dpt = '17' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Pierre (06260)' AND category_id = 1)
WHERE commune_nom = 'Saint-Pierre' AND dpt = '04' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Venterol (05130)' AND category_id = 1)
WHERE commune_nom = 'Venterol' AND dpt = '04' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Ferrière (85280)' AND category_id = 1)
WHERE commune_nom = 'Les Moulins' AND dpt = '85' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Plémet (22210)' AND category_id = 1)
WHERE commune_nom = 'Les Moulins' AND dpt = '22' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Ferrière (37110)' AND category_id = 1)
WHERE commune_nom = 'Les Moulins' AND dpt = '37' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Ferrière (38580)' AND category_id = 1)
WHERE commune_nom = 'Les Moulins' AND dpt = '38' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Touzac (46700)' AND category_id = 1)
WHERE commune_nom = 'Bellevigne' AND dpt = '46' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Cintray (28300)' AND category_id = 1)
WHERE commune_nom = 'Breteuil' AND dpt = '28' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Orlu (09110)' AND category_id = 1)
WHERE commune_nom = 'Gommerville' AND dpt = '09' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Waldighofen (68640)' AND category_id = 1)
WHERE commune_nom = 'Waldighoffen' AND dpt = '68' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Olizy (51700)' AND category_id = 1)
WHERE commune_nom = 'Olizy-Violaine' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bouloc-en-Quercy (82110)' AND category_id = 1)
WHERE commune_nom = 'Bouloc' AND dpt = '82' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Clara-Villerach (66500)' AND category_id = 1)
WHERE commune_nom = 'Clara' AND dpt = '66' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Hérouville-en-Vexin (95300)' AND category_id = 1)
WHERE commune_nom = 'Hérouville' AND dpt = '95' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Langon-sur-Cher (41320)' AND category_id = 1)
WHERE commune_nom = 'Langon' AND dpt = '41' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Luc-la-Primaube (12450)' AND category_id = 1)
WHERE commune_nom = 'Luc' AND dpt = '12' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Allemond (38114)' AND category_id = 1)
WHERE commune_nom = 'Allemont' AND dpt = '38' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Pringy (77310)' AND category_id = 1)
WHERE commune_nom = 'Annecy' AND dpt = '77' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Pringy (51300)' AND category_id = 1)
WHERE commune_nom = 'Annecy' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Aubigny (80800)' AND category_id = 1)
WHERE commune_nom = 'Aubigny-Les Clouzeaux' AND dpt = '80' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Aubigny (14700)' AND category_id = 1)
WHERE commune_nom = 'Aubigny-Les Clouzeaux' AND dpt = '14' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Aubigny (79390)' AND category_id = 1)
WHERE commune_nom = 'Aubigny-Les Clouzeaux' AND dpt = '79' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Aubigny (03460)' AND category_id = 1)
WHERE commune_nom = 'Aubigny-Les Clouzeaux' AND dpt = '03' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Dommartin (80440)' AND category_id = 1)
WHERE commune_nom = 'Bâgé-Dommartin' AND dpt = '80' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Dommartin (69380)' AND category_id = 1)
WHERE commune_nom = 'Bâgé-Dommartin' AND dpt = '69' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Dommartin (25300)' AND category_id = 1)
WHERE commune_nom = 'Bâgé-Dommartin' AND dpt = '25' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Dommartin (58120)' AND category_id = 1)
WHERE commune_nom = 'Bâgé-Dommartin' AND dpt = '58' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ballon (17290)' AND category_id = 1)
WHERE commune_nom = 'Ballon-Saint Mars' AND dpt = '17' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fougeré (85480)' AND category_id = 1)
WHERE commune_nom = 'Baugé-en-Anjou' AND dpt = '85' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Colombe (50800)' AND category_id = 1)
WHERE commune_nom = 'Beauce la Romaine' AND dpt = '50' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Cours (69240,69470)' AND category_id = 1)
WHERE commune_nom = 'Bellefont-La Rauze' AND dpt = '69' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Cours (47360)' AND category_id = 1)
WHERE commune_nom = 'Bellefont-La Rauze' AND dpt = '47' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Cours (79220)' AND category_id = 1)
WHERE commune_nom = 'Bellefont-La Rauze' AND dpt = '79' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Marsal (57630)' AND category_id = 1)
WHERE commune_nom = 'Bellegarde-Marsal' AND dpt = '57' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bellegarde (45270)' AND category_id = 1)
WHERE commune_nom = 'Bellegarde-Marsal' AND dpt = '45' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bellegarde (30127)' AND category_id = 1)
WHERE commune_nom = 'Bellegarde-Marsal' AND dpt = '30' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Cyr (87310)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '87' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaumont (32100)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '32' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaumont (07110)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '07' AND type = 'conseiller_municipal'
  AND canonical IN (
                            'Jacques-MATHIEU-1958-01-08-conseiller_municipal-NC-2014-03-23',
                            'Cédric-MERCIER-1983-02-14-conseiller_municipal-NC-2014-03-23',
                            'Annie-MAHIEUX-1946-04-06-conseiller_municipal-NC-2014-03-23',
                            'Danielle-DECAVATA-1944-07-15-conseiller_municipal-NC-2014-03-23',
                            'Jean-Rémi-DURAND-GASSELIN-1947-03-09-conseiller_municipal-NC-2014-03-23',
                            'Agnès-AUDIBERT-1960-10-26-conseiller_municipal-NC-2014-03-23',
                            'Loïse-COLTEL-1980-11-07-conseiller_municipal-NC-2014-03-23',
                            'Jacqueline-MIELLE-1943-08-11-conseiller_municipal-NC-2014-03-23',
                            'Pascal-WALDSCHMIDT-1949-10-29-membre_EPCI-DVG-2014-03-28',
                            'Antoine-WALDSCHMIDT-1983-03-15-conseiller_municipal-NC-2014-03-23',
                            'Pascal-WALDSCHMIDT-1949-10-29-conseiller_municipal-DVG-2014-03-23',
                            'Emmanuel-PICARD-1971-05-15-conseiller_municipal-NC-2014-03-23'
                           )
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Cyr (07430)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '07' AND type = 'conseiller_municipal' AND zone_id IS NULL
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaumont (54470)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '54' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Cyr (71240)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Cyr (50310)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '50' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaumont (89250)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '89' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaumont (63110)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '63' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sulpice (53360)' AND category_id = 1)
WHERE commune_nom = 'Blaison-Saint-Sulpice' AND dpt = '53' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sulpice (63760)' AND category_id = 1)
WHERE commune_nom = 'Blaison-Saint-Sulpice' AND dpt = '63' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sulpice (01340)' AND category_id = 1)
WHERE commune_nom = 'Blaison-Saint-Sulpice' AND dpt = '01' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sulpice (58270)' AND category_id = 1)
WHERE commune_nom = 'Blaison-Saint-Sulpice' AND dpt = '58' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sulpice (73160)' AND category_id = 1)
WHERE commune_nom = 'Blaison-Saint-Sulpice' AND dpt = '73' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sulpice (70110)' AND category_id = 1)
WHERE commune_nom = 'Blaison-Saint-Sulpice' AND dpt = '70' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sulpice (60430)' AND category_id = 1)
WHERE commune_nom = 'Blaison-Saint-Sulpice' AND dpt = '60' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sulpice (46160)' AND category_id = 1)
WHERE commune_nom = 'Blaison-Saint-Sulpice' AND dpt = '46' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Coutures (24320)' AND category_id = 1)
WHERE commune_nom = 'Brissac Loire Aubance' AND dpt = '24' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Coutures (33580)' AND category_id = 1)
WHERE commune_nom = 'Brissac Loire Aubance' AND dpt = '33' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Coutures (82210)' AND category_id = 1)
WHERE commune_nom = 'Brissac Loire Aubance' AND dpt = '82' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Avrilly (61700)' AND category_id = 1)
WHERE commune_nom = 'Chambois' AND dpt = '61' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Avrilly (03130)' AND category_id = 1)
WHERE commune_nom = 'Chambois' AND dpt = '03' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sérigny (86230)' AND category_id = 1)
WHERE commune_nom = 'Belforêt-en-Perche' AND dpt = '86' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saligny (89100)' AND category_id = 1)
WHERE commune_nom = 'Bellevigny' AND dpt = '89' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Benque (31420)' AND category_id = 1)
WHERE commune_nom = 'Benqué-Molère' AND dpt = '31' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Vauchamps (51210)' AND category_id = 1)
WHERE commune_nom = 'Bouclans' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chirac (16150)' AND category_id = 1)
WHERE commune_nom = 'Bourgs sur Colagne' AND dpt = '16' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Livry (58240)' AND category_id = 1)
WHERE commune_nom = 'Caumont-sur-Aure' AND dpt = '58' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chancia (01590)' AND category_id = 1)
WHERE commune_nom = 'Chancia' AND dpt = '39' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Osse (25360)' AND category_id = 1)
WHERE commune_nom = 'Châteaugiron' AND dpt = '25' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sébastien (23160)' AND category_id = 1)
WHERE commune_nom = 'Châtel-en-Trièves' AND dpt = '23' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Christine (63390)' AND category_id = 1)
WHERE commune_nom = 'Chemillé-en-Anjou' AND dpt = '63' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Mouzens (81470)' AND category_id = 1)
WHERE commune_nom = 'Coux et Bigaroque-Mouzens' AND dpt = '81' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Curbans (05110)' AND category_id = 1)
WHERE commune_nom = 'Curbans' AND dpt = '04' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Domfront (60420)' AND category_id = 1)
WHERE commune_nom = 'Domfront en Poiraie' AND dpt = '60' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Douchy (02590)' AND category_id = 1)
WHERE commune_nom = 'Douchy-Montcorbon' AND dpt = '02' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Florence (33350)' AND category_id = 1)
WHERE commune_nom = 'Essarts en Bocage' AND dpt = '33' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Louvières (52800)' AND category_id = 1)
WHERE commune_nom = 'Formigny La Bataille' AND dpt = '52' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fougueyrolles (33220)' AND category_id = 1)
WHERE commune_nom = 'Fougueyrolles' AND dpt = '24' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Theil (03240)' AND category_id = 1)
WHERE commune_nom = 'Gonneville-Le Theil' AND dpt = '03' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chambois (27240)' AND category_id = 1)
WHERE commune_nom = 'Gouffern en Auge' AND dpt = '27' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Messac (17130)' AND category_id = 1)
WHERE commune_nom = 'Guipry-Messac' AND dpt = '17' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Maillet (36340)' AND category_id = 1)
WHERE commune_nom = 'Haut-Bocage' AND dpt = '36' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lucé (28110)' AND category_id = 1)
WHERE commune_nom = 'Juvigny Val d''Andaine' AND dpt = '28' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lajoux (01410,39310)' AND category_id = 1)
WHERE commune_nom = 'Lajoux' AND dpt = '39' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lavancia-Epercy (01590)' AND category_id = 1)
WHERE commune_nom = 'Lavancia-Epercy' AND dpt = '39' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Laveyrune (48250)' AND category_id = 1)
WHERE commune_nom = 'Laveyrune' AND dpt = '07' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Cravant (45190)' AND category_id = 1)
WHERE commune_nom = 'Deux Rivières' AND dpt = '45' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Pers (79190)' AND category_id = 1)
WHERE commune_nom = 'Le Rouget-Pers' AND dpt = '79' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Martin-sur-Ocre (45500)' AND category_id = 1)
WHERE commune_nom = 'Le Val d''Ocre' AND dpt = '45' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Fresne (51240)' AND category_id = 1)
WHERE commune_nom = 'Le Val-Doré' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Cherré (72400)' AND category_id = 1)
WHERE commune_nom = 'Les Hauts d''Anjou' AND dpt = '72' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fontenailles (77370)' AND category_id = 1)
WHERE commune_nom = 'Les Hauts de Forterre' AND dpt = '77' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Cernin (15310)' AND category_id = 1)
WHERE commune_nom = 'Les Pechs du Vers' AND dpt = '15' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Montainville (78124)' AND category_id = 1)
WHERE commune_nom = 'Les Villages Vovéens' AND dpt = '78' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Leyvaux (43450)' AND category_id = 1)
WHERE commune_nom = 'Leyvaux' AND dpt = '15' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Chapelle-Saint-Sauveur (71310)' AND category_id = 1)
WHERE commune_nom = 'Loireauxence' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Trémont (61390)' AND category_id = 1)
WHERE commune_nom = 'Lys-Haut-Layon' AND dpt = '61' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chaudefontaine (51800)' AND category_id = 1)
WHERE commune_nom = 'Marchaux-Chaudefontaine' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Mareuil (16170)' AND category_id = 1)
WHERE commune_nom = 'Mareuil en Périgord' AND dpt = '16' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Pommeraye (14690)' AND category_id = 1)
WHERE commune_nom = 'Mauges-sur-Loire' AND dpt = '14' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Belvézet (30580)' AND category_id = 1)
WHERE commune_nom = 'Mont Lozère et Goulet' AND dpt = '30' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Coulombs (28210)' AND category_id = 1)
WHERE commune_nom = 'Moulins en Bessin' AND dpt = '28' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chevry (01170)' AND category_id = 1)
WHERE commune_nom = 'Moyon Villages' AND dpt = '01' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Oradour (16140)' AND category_id = 1)
WHERE commune_nom = 'Neuvéglise-sur-Truyère' AND dpt = '16' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lasse (64220)' AND category_id = 1)
WHERE commune_nom = 'Noyant-Villages' AND dpt = '64' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Orval (18200)' AND category_id = 1)
WHERE commune_nom = 'Orval sur Sienne' AND dpt = '18' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Dancé (42260)' AND category_id = 1)
WHERE commune_nom = 'Perche en Nocé' AND dpt = '42' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Percy (38930)' AND category_id = 1)
WHERE commune_nom = 'Percy-en-Normandie' AND dpt = '38' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Assigny (18260)' AND category_id = 1)
WHERE commune_nom = 'Petit-Caux' AND dpt = '18' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Amfreville (14860)' AND category_id = 1)
WHERE commune_nom = 'Picauville' AND dpt = '14' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Pontis (05160)' AND category_id = 1)
WHERE commune_nom = 'Pontis' AND dpt = '04' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Macey (10300)' AND category_id = 1)
WHERE commune_nom = 'Pontorson' AND dpt = '10' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Port-Sainte-Foy-et-Ponchapt (33220)' AND category_id = 1)
WHERE commune_nom = 'Port-Sainte-Foy-et-Ponchapt' AND dpt = '24' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Samson (14670)' AND category_id = 1)
WHERE commune_nom = 'Pré-en-Pail-Saint-Samson' AND dpt = '14' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Riboux (13780)' AND category_id = 1)
WHERE commune_nom = 'Riboux' AND dpt = '83' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Boos (76520)' AND category_id = 1)
WHERE commune_nom = 'Rion-des-Landes' AND dpt = '76' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Romagny (68210)' AND category_id = 1)
WHERE commune_nom = 'Romagny Fontenay' AND dpt = '68' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fréville (88350)' AND category_id = 1)
WHERE commune_nom = 'Saint Martin de l''If' AND dpt = '88' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Eulien (52100)' AND category_id = 1)
WHERE commune_nom = 'Saint-Eulien' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Boissey (01190,01380)' AND category_id = 1)
WHERE commune_nom = 'Saint-Pierre-en-Auge' AND dpt = '01' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Pierre-Laval (42620)' AND category_id = 1)
WHERE commune_nom = 'Saint-Pierre-Laval' AND dpt = '03' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Dame-Marie (61130)' AND category_id = 1)
WHERE commune_nom = 'Sainte-Marie-d''Attez' AND dpt = '61' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sapignicourt (52100)' AND category_id = 1)
WHERE commune_nom = 'Sapignicourt' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Torfou (91730)' AND category_id = 1)
WHERE commune_nom = 'Sèvremoine' AND dpt = '91' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ligueux (33220)' AND category_id = 1)
WHERE commune_nom = 'Sorges et Ligueux en Périgord' AND dpt = '33' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Larche (19600)' AND category_id = 1)
WHERE commune_nom = 'Val d''Oronaye' AND dpt = '19' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Coulanges (03470)' AND category_id = 1)
WHERE commune_nom = 'Valloire-sur-Cisse' AND dpt = '03' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Châtonnay (38440)' AND category_id = 1)
WHERE commune_nom = 'Valzin en Petite Montagne' AND dpt = '38' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Villebois-les-Pins (05700)' AND category_id = 1)
WHERE commune_nom = 'Villebois-les-Pins' AND dpt = '26' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sacy (51500)' AND category_id = 1)
WHERE commune_nom = 'Vermenton' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Coulonces (61160)' AND category_id = 1)
WHERE commune_nom = 'Vire Normandie' AND dpt = '61' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Viré (71260)' AND category_id = 1)
WHERE commune_nom = 'Vire Normandie' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Pin (03130)' AND category_id = 1)
WHERE commune_nom = 'Villages du Lac de Paladru' AND dpt = '03' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Pin (14590)' AND category_id = 1)
WHERE commune_nom = 'Villages du Lac de Paladru' AND dpt = '14' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Pin (17210)' AND category_id = 1)
WHERE commune_nom = 'Villages du Lac de Paladru' AND dpt = '17' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Pin (30330)' AND category_id = 1)
WHERE commune_nom = 'Villages du Lac de Paladru' AND dpt = '30' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'name' AND category_id = 1)
WHERE commune_nom = 'Villages du Lac de Paladru' AND dpt = '00' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Pin (44540)' AND category_id = 1)
WHERE commune_nom = 'Villages du Lac de Paladru' AND dpt = '44' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Pin (77181)' AND category_id = 1)
WHERE commune_nom = 'Villages du Lac de Paladru' AND dpt = '77' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Pin (79140)' AND category_id = 1)
WHERE commune_nom = 'Villages du Lac de Paladru' AND dpt = '79' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Le Pin (82340)' AND category_id = 1)
WHERE commune_nom = 'Villages du Lac de Paladru' AND dpt = '82' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fontenay (36150)' AND category_id = 1)
WHERE commune_nom = 'Vexin-sur-Epte' AND dpt = '36' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fontenay (71120)' AND category_id = 1)
WHERE commune_nom = 'Vexin-sur-Epte' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fontenay (76290)' AND category_id = 1)
WHERE commune_nom = 'Vexin-sur-Epte' AND dpt = '76' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fontenay (88600)' AND category_id = 1)
WHERE commune_nom = 'Vexin-sur-Epte' AND dpt = '88' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Francheville (21440)' AND category_id = 1)
WHERE commune_nom = 'Verneuil d''Avre et d''Iton' AND dpt = '21' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Francheville (39230)' AND category_id = 1)
WHERE commune_nom = 'Verneuil d''Avre et d''Iton' AND dpt = '39' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Francheville (51240)' AND category_id = 1)
WHERE commune_nom = 'Verneuil d''Avre et d''Iton' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Francheville (54200)' AND category_id = 1)
WHERE commune_nom = 'Verneuil d''Avre et d''Iton' AND dpt = '54' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Francheville (61570)' AND category_id = 1)
WHERE commune_nom = 'Verneuil d''Avre et d''Iton' AND dpt = '61' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Francheville (69340)' AND category_id = 1)
WHERE commune_nom = 'Verneuil d''Avre et d''Iton' AND dpt = '69' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Neuilly (27730)' AND category_id = 1)
WHERE commune_nom = 'Valravillon' AND dpt = '27' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Neuilly (58420)' AND category_id = 1)
WHERE commune_nom = 'Valravillon' AND dpt = '58' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Villemer (77250)' AND category_id = 1)
WHERE commune_nom = 'Valravillon' AND dpt = '77' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Maumusson (82120)' AND category_id = 1)
WHERE commune_nom = 'Vallons-de-l''Erdre' AND dpt = '82' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sulpice-des-Landes (35390)' AND category_id = 1)
WHERE commune_nom = 'Vallons-de-l''Erdre' AND dpt = '35' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Agnan (58230)' AND category_id = 1)
WHERE commune_nom = 'Vallées en Champagne' AND dpt = '58' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Agnan (71160)' AND category_id = 1)
WHERE commune_nom = 'Vallées en Champagne' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Agnan (81500)' AND category_id = 1)
WHERE commune_nom = 'Vallées en Champagne' AND dpt = '81' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Agnan (89340)' AND category_id = 1)
WHERE commune_nom = 'Vallées en Champagne' AND dpt = '89' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Marie (08400)' AND category_id = 1)
WHERE commune_nom = 'Valdoule' AND dpt = '08' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Marie (15230)' AND category_id = 1)
WHERE commune_nom = 'Valdoule' AND dpt = '15' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Marie (25113)' AND category_id = 1)
WHERE commune_nom = 'Valdoule' AND dpt = '25' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Marie (32200)' AND category_id = 1)
WHERE commune_nom = 'Valdoule' AND dpt = '32' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Marie (35600)' AND category_id = 1)
WHERE commune_nom = 'Valdoule' AND dpt = '35' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Marie (58330)' AND category_id = 1)
WHERE commune_nom = 'Valdoule' AND dpt = '58' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Marie (65370)' AND category_id = 1)
WHERE commune_nom = 'Valdoule' AND dpt = '65' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Montmorin (63160)' AND category_id = 1)
WHERE commune_nom = 'Valdoule' AND dpt = '63' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Marie-la-Mer (66470)' AND category_id = 1)
WHERE commune_nom = 'Valdoule' AND dpt = '66' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Pierres (28130)' AND category_id = 1)
WHERE commune_nom = 'Valdallière' AND dpt = '28' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Presles (38680)' AND category_id = 1)
WHERE commune_nom = 'Valdallière' AND dpt = '38' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Presles (95590)' AND category_id = 1)
WHERE commune_nom = 'Valdallière' AND dpt = '95' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Rully (60810)' AND category_id = 1)
WHERE commune_nom = 'Valdallière' AND dpt = '60' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Rully (71150)' AND category_id = 1)
WHERE commune_nom = 'Valdallière' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Montchamp (15100)' AND category_id = 1)
WHERE commune_nom = 'Valdallière' AND dpt = '15' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Burcy (77760)' AND category_id = 1)
WHERE commune_nom = 'Valdallière' AND dpt = '77' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Billy (03260)' AND category_id = 1)
WHERE commune_nom = 'Valambray' AND dpt = '03' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Billy (41130)' AND category_id = 1)
WHERE commune_nom = 'Valambray' AND dpt = '41' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Conteville (60360)' AND category_id = 1)
WHERE commune_nom = 'Valambray' AND dpt = '60' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Conteville (76390)' AND category_id = 1)
WHERE commune_nom = 'Valambray' AND dpt = '76' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Conteville (80370)' AND category_id = 1)
WHERE commune_nom = 'Valambray' AND dpt = '80' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Conteville (27210)' AND category_id = 1)
WHERE commune_nom = 'Valambray' AND dpt = '27' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Vincelles (51700)' AND category_id = 1)
WHERE commune_nom = 'Val-Sonnette' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Vincelles (71500)' AND category_id = 1)
WHERE commune_nom = 'Val-Sonnette' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Vincelles (89290)' AND category_id = 1)
WHERE commune_nom = 'Val-Sonnette' AND dpt = '89' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Cécile (50800)' AND category_id = 1)
WHERE commune_nom = 'Val-Fouzon' AND dpt = '50' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Cécile (71250)' AND category_id = 1)
WHERE commune_nom = 'Val-Fouzon' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Cécile (85110)' AND category_id = 1)
WHERE commune_nom = 'Val-Fouzon' AND dpt = '85' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (05700)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '05' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (17210)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '17' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (25300)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '25' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (35134)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '35' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (33350)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '33' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (40700)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '40' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (46120)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '46' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (50390)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '50' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (69560)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '69' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (76460)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '76' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (77650)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '77' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Colombe (89440)' AND category_id = 1)
WHERE commune_nom = 'Val-de-Bonnieure' AND dpt = '89' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Julien (21490)' AND category_id = 1)
WHERE commune_nom = 'Val Suran' AND dpt = '21' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Julien (22940)' AND category_id = 1)
WHERE commune_nom = 'Val Suran' AND dpt = '22' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Julien (34390)' AND category_id = 1)
WHERE commune_nom = 'Val Suran' AND dpt = '34' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Julien (69640)' AND category_id = 1)
WHERE commune_nom = 'Val Suran' AND dpt = '69' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Julien (83560)' AND category_id = 1)
WHERE commune_nom = 'Val Suran' AND dpt = '83' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Julien (88410)' AND category_id = 1)
WHERE commune_nom = 'Val Suran' AND dpt = '88' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Julien-sur-Garonne (31220)' AND category_id = 1)
WHERE commune_nom = 'Val Suran' AND dpt = '31' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Salignac (04290)' AND category_id = 1)
WHERE commune_nom = 'Val de Virvée' AND dpt = '04' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Antoine (15220)' AND category_id = 1)
WHERE commune_nom = 'Val de Virvée' AND dpt = '15' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Antoine (25370)' AND category_id = 1)
WHERE commune_nom = 'Val de Virvée' AND dpt = '25' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Antoine (32340)' AND category_id = 1)
WHERE commune_nom = 'Val de Virvée' AND dpt = '32' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Dampierre (10240)' AND category_id = 1)
WHERE commune_nom = 'Val de Drôme' AND dpt = '10' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Dampierre (39700)' AND category_id = 1)
WHERE commune_nom = 'Val de Drôme' AND dpt = '39' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Dampierre (52360)' AND category_id = 1)
WHERE commune_nom = 'Val de Drôme' AND dpt = '52' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Just (01250)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '01' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Just (18340)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '18' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Just (24320)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '24' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Just (34400)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '34' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Just (35550)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '35' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Just (63600)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '63' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Faverolles (02600)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '02' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Loubaresse (07110)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '07' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Faverolles (28210)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '28' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Faverolles-en-Berry (36360)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '36' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Faverolles (52260)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '52' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Faverolles (61600)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '61' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Faverolles (80500)' AND category_id = 1)
WHERE commune_nom = 'Val d''Arcomie' AND dpt = '80' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Champs (02670)' AND category_id = 1)
WHERE commune_nom = 'Tourouvre au Perche' AND dpt = '02' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Champs (63440)' AND category_id = 1)
WHERE commune_nom = 'Tourouvre au Perche' AND dpt = '63' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lignerolles (03410)' AND category_id = 1)
WHERE commune_nom = 'Tourouvre au Perche' AND dpt = '03' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lignerolles (21520)' AND category_id = 1)
WHERE commune_nom = 'Tourouvre au Perche' AND dpt = '21' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lignerolles (27220)' AND category_id = 1)
WHERE commune_nom = 'Tourouvre au Perche' AND dpt = '27' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lignerolles (36160)' AND category_id = 1)
WHERE commune_nom = 'Tourouvre au Perche' AND dpt = '36' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Tourouvre au Perche (61190)' AND category_id = 1)
WHERE commune_nom = 'Tourouvre au Perche' AND dpt = '61' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lassy (35580)' AND category_id = 1)
WHERE commune_nom = 'Terres de Druance' AND dpt = '35' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lassy (95270)' AND category_id = 1)
WHERE commune_nom = 'Terres de Druance' AND dpt = '95' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Jean-le-Blanc (45650)' AND category_id = 1)
WHERE commune_nom = 'Terres de Druance' AND dpt = '45' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Martin-du-Bois (33910)' AND category_id = 1)
WHERE commune_nom = 'Segré-en-Anjou Bleu' AND dpt = '33' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Marans (17230)' AND category_id = 1)
WHERE commune_nom = 'Segré-en-Anjou Bleu' AND dpt = '17' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Orville (21260)' AND category_id = 1)
WHERE commune_nom = 'Sap-en-Auge' AND dpt = '21' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Orville (36210)' AND category_id = 1)
WHERE commune_nom = 'Sap-en-Auge' AND dpt = '36' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Orville (45390)' AND category_id = 1)
WHERE commune_nom = 'Sap-en-Auge' AND dpt = '45' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Orville (62760)' AND category_id = 1)
WHERE commune_nom = 'Sap-en-Auge' AND dpt = '62' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Suzanne (09130)' AND category_id = 1)
WHERE commune_nom = 'Sainte-Suzanne-et-Chammes' AND dpt = '09' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Suzanne (25630)' AND category_id = 1)
WHERE commune_nom = 'Sainte-Suzanne-et-Chammes' AND dpt = '25' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Amand (23200)' AND category_id = 1)
WHERE commune_nom = 'Saint-Amand-Villages' AND dpt = '23' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Amand (62760)' AND category_id = 1)
WHERE commune_nom = 'Saint-Amand-Villages' AND dpt = '62' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaulieu (07460)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '07' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaulieu (15270)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '15' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaulieu (21510)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '21' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaulieu (34160)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '34' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaulieu (36310)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '36' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaulieu (38470)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '38' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaulieu (43800)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '43' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaulieu (58420)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '58' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaulieu (61190)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '61' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaulieu (63570)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '63' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Campeaux (60220)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '60' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Montchauvet (78790)' AND category_id = 1)
WHERE commune_nom = 'Souleuvre en Bocage' AND dpt = '78' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sauveur (05200)' AND category_id = 1)
WHERE commune_nom = 'Senillé-Saint-Sauveur' AND dpt = '05' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sauveur (21270)' AND category_id = 1)
WHERE commune_nom = 'Senillé-Saint-Sauveur' AND dpt = '21' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sauveur (24520)' AND category_id = 1)
WHERE commune_nom = 'Senillé-Saint-Sauveur' AND dpt = '24' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sauveur (29400)' AND category_id = 1)
WHERE commune_nom = 'Senillé-Saint-Sauveur' AND dpt = '29' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sauveur (31790)' AND category_id = 1)
WHERE commune_nom = 'Senillé-Saint-Sauveur' AND dpt = '31' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sauveur (33250)' AND category_id = 1)
WHERE commune_nom = 'Senillé-Saint-Sauveur' AND dpt = '33' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sauveur (38160)' AND category_id = 1)
WHERE commune_nom = 'Senillé-Saint-Sauveur' AND dpt = '38' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sauveur (54480)' AND category_id = 1)
WHERE commune_nom = 'Senillé-Saint-Sauveur' AND dpt = '54' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sauveur (60320)' AND category_id = 1)
WHERE commune_nom = 'Senillé-Saint-Sauveur' AND dpt = '60' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sauveur (70300)' AND category_id = 1)
WHERE commune_nom = 'Senillé-Saint-Sauveur' AND dpt = '70' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Sauveur (80470)' AND category_id = 1)
WHERE commune_nom = 'Senillé-Saint-Sauveur' AND dpt = '80' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Vers (71240)' AND category_id = 1)
WHERE commune_nom = 'Saint Géry-Vers' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Vers (74160)' AND category_id = 1)
WHERE commune_nom = 'Saint Géry-Vers' AND dpt = '74' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Géry (24400)' AND category_id = 1)
WHERE commune_nom = 'Saint Géry-Vers' AND dpt = '24' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Belleville (54940)' AND category_id = 1)
WHERE commune_nom = 'Plaine-d''Argenson' AND dpt = '54' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Belleville (69220)' AND category_id = 1)
WHERE commune_nom = 'Plaine-d''Argenson' AND dpt = '69' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Siméon (27560)' AND category_id = 1)
WHERE commune_nom = 'Passais Villages' AND dpt = '27' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Siméon (77169)' AND category_id = 1)
WHERE commune_nom = 'Passais Villages' AND dpt = '77' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beauvilliers (28150)' AND category_id = 1)
WHERE commune_nom = 'Oucques La Nouvelle' AND dpt = '28' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beauvilliers (89630)' AND category_id = 1)
WHERE commune_nom = 'Oucques La Nouvelle' AND dpt = '89' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Baigneaux (33760)' AND category_id = 1)
WHERE commune_nom = 'Oucques La Nouvelle' AND dpt = '33' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Celles (09000)' AND category_id = 1)
WHERE commune_nom = 'Neussargues en Pinatelle' AND dpt = '09' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Celles (17520)' AND category_id = 1)
WHERE commune_nom = 'Neussargues en Pinatelle' AND dpt = '17' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Celles (24600)' AND category_id = 1)
WHERE commune_nom = 'Neussargues en Pinatelle' AND dpt = '24' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Celles (34700)' AND category_id = 1)
WHERE commune_nom = 'Neussargues en Pinatelle' AND dpt = '34' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Anastasie (30190)' AND category_id = 1)
WHERE commune_nom = 'Neussargues en Pinatelle' AND dpt = '30' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fontanès (30250)' AND category_id = 1)
WHERE commune_nom = 'Naussac-Fontanes' AND dpt = '30' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fontanès (34270)' AND category_id = 1)
WHERE commune_nom = 'Naussac-Fontanes' AND dpt = '34' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fontanès (42140)' AND category_id = 1)
WHERE commune_nom = 'Naussac-Fontanes' AND dpt = '42' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fontanes (46230)' AND category_id = 1)
WHERE commune_nom = 'Naussac-Fontanes' AND dpt = '46' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Naussac (12700)' AND category_id = 1)
WHERE commune_nom = 'Naussac-Fontanes' AND dpt = '12' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Croix (01120)' AND category_id = 1)
WHERE commune_nom = 'Montcuq-en-Quercy-Blanc' AND dpt = '01' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Croix (02820)' AND category_id = 1)
WHERE commune_nom = 'Montcuq-en-Quercy-Blanc' AND dpt = '02' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Croix (12260)' AND category_id = 1)
WHERE commune_nom = 'Montcuq-en-Quercy-Blanc' AND dpt = '12' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Croix (24440)  ' AND category_id = 1)
WHERE commune_nom = 'Montcuq-en-Quercy-Blanc' AND dpt = '24' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Croix (26150)' AND category_id = 1)
WHERE commune_nom = 'Montcuq-en-Quercy-Blanc' AND dpt = '26' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Croix (71470)' AND category_id = 1)
WHERE commune_nom = 'Montcuq-en-Quercy-Blanc' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Croix (81150)' AND category_id = 1)
WHERE commune_nom = 'Montcuq-en-Quercy-Blanc' AND dpt = '81' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaumesnil (14380)' AND category_id = 1)
WHERE commune_nom = 'Mesnil-en-Ouche' AND dpt = '14' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Gouttières (63390)' AND category_id = 1)
WHERE commune_nom = 'Mesnil-en-Ouche' AND dpt = '63' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Marigny (03210)' AND category_id = 1)
WHERE commune_nom = 'Marigny-Le-Lozon' AND dpt = '03' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Marigny (39130)' AND category_id = 1)
WHERE commune_nom = 'Marigny-Le-Lozon' AND dpt = '39' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Marigny (51230)' AND category_id = 1)
WHERE commune_nom = 'Marigny-Le-Lozon' AND dpt = '51' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Marigny (71300)' AND category_id = 1)
WHERE commune_nom = 'Marigny-Le-Lozon' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Marigny (79360)' AND category_id = 1)
WHERE commune_nom = 'Marigny-Le-Lozon' AND dpt = '79' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chanteloup (35150)' AND category_id = 1)
WHERE commune_nom = 'Marbois' AND dpt = '35' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chanteloup (50510)' AND category_id = 1)
WHERE commune_nom = 'Marbois' AND dpt = '50' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chanteloup (79320)' AND category_id = 1)
WHERE commune_nom = 'Marbois' AND dpt = '79' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Les Essarts (41800)' AND category_id = 1)
WHERE commune_nom = 'Marbois' AND dpt = '41' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Vareilles (23300)' AND category_id = 1)
WHERE commune_nom = 'Les Vallées de la Vanne' AND dpt = '23' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Vareilles (71800)' AND category_id = 1)
WHERE commune_nom = 'Les Vallées de la Vanne' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chazelles (15500)' AND category_id = 1)
WHERE commune_nom = 'Les Trois Châteaux' AND dpt = '15' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chazelles (16380)' AND category_id = 1)
WHERE commune_nom = 'Les Trois Châteaux' AND dpt = '16' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chazelles (43300)' AND category_id = 1)
WHERE commune_nom = 'Les Trois Châteaux' AND dpt = '43' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Grèzes (43170)' AND category_id = 1)
WHERE commune_nom = 'Les Coteaux Périgourdins' AND dpt = '43' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Grèzes (46320)' AND category_id = 1)
WHERE commune_nom = 'Les Coteaux Périgourdins' AND dpt = '46' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Grèzes (48100)' AND category_id = 1)
WHERE commune_nom = 'Les Coteaux Périgourdins' AND dpt = '48' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Brion (01460)' AND category_id = 1)
WHERE commune_nom = 'Les Bois d''Anjou' AND dpt = '01' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Brion (36110)' AND category_id = 1)
WHERE commune_nom = 'Les Bois d''Anjou' AND dpt = '36' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Brion (38590)' AND category_id = 1)
WHERE commune_nom = 'Les Bois d''Anjou' AND dpt = '38' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Brion (48310)' AND category_id = 1)
WHERE commune_nom = 'Les Bois d''Anjou' AND dpt = '48' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Brion (71190)' AND category_id = 1)
WHERE commune_nom = 'Les Bois d''Anjou' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Brion (86160)' AND category_id = 1)
WHERE commune_nom = 'Les Bois d''Anjou' AND dpt = '86' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Brion (89400)' AND category_id = 1)
WHERE commune_nom = 'Les Bois d''Anjou' AND dpt = '89' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Georges-du-Bois (17700)' AND category_id = 1)
WHERE commune_nom = 'Les Bois d''Anjou' AND dpt = '17' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Georges-du-Bois (72700)' AND category_id = 1)
WHERE commune_nom = 'Les Bois d''Anjou' AND dpt = '72' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Cyprien (19130)' AND category_id = 1)
WHERE commune_nom = 'Lendou-en-Quercy' AND dpt = '19' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Cyprien (24220)' AND category_id = 1)
WHERE commune_nom = 'Lendou-en-Quercy' AND dpt = '24' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Cyprien (42160)' AND category_id = 1)
WHERE commune_nom = 'Lendou-en-Quercy' AND dpt = '42' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Cyprien (66750)' AND category_id = 1)
WHERE commune_nom = 'Lendou-en-Quercy' AND dpt = '66' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ferrières (17170)' AND category_id = 1)
WHERE commune_nom = 'Le Teilleul' AND dpt = '17' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ferrières (54210)' AND category_id = 1)
WHERE commune_nom = 'Le Teilleul' AND dpt = '54' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ferrières (60420)' AND category_id = 1)
WHERE commune_nom = 'Le Teilleul' AND dpt = '60' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ferrières (65560)' AND category_id = 1)
WHERE commune_nom = 'Le Teilleul' AND dpt = '65' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ferrières (80470)' AND category_id = 1)
WHERE commune_nom = 'Le Teilleul' AND dpt = '80' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ferrières-en-Gâtinais (45210)' AND category_id = 1)
WHERE commune_nom = 'Le Teilleul' AND dpt = '45' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Sainte-Marie-du-Bois (53110)' AND category_id = 1)
WHERE commune_nom = 'Le Teilleul' AND dpt = '53' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Coudray (27150)' AND category_id = 1)
WHERE commune_nom = 'Le Malesherbois' AND dpt = '27' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Coudray (53200)' AND category_id = 1)
WHERE commune_nom = 'Le Malesherbois' AND dpt = '53' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Mainvilliers (28300)' AND category_id = 1)
WHERE commune_nom = 'Le Malesherbois' AND dpt = '28' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lasserre (09230)' AND category_id = 1)
WHERE commune_nom = 'Lasserre-Pradère' AND dpt = '09' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lasserre (47600)' AND category_id = 1)
WHERE commune_nom = 'Lasserre-Pradère' AND dpt = '47' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Lasserre (64350)' AND category_id = 1)
WHERE commune_nom = 'Lasserre-Pradère' AND dpt = '64' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Les Essards (16210)' AND category_id = 1)
WHERE commune_nom = 'Langeais' AND dpt = '16' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Les Essards (17250)' AND category_id = 1)
WHERE commune_nom = 'Langeais' AND dpt = '17' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Massy (76270)' AND category_id = 1)
WHERE commune_nom = 'La Vineuse sur Fregande' AND dpt = '76' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Massy (91300)' AND category_id = 1)
WHERE commune_nom = 'La Vineuse sur Fregande' AND dpt = '91' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Acqueville (14220)' AND category_id = 1)
WHERE commune_nom = 'La Hague' AND dpt = '14' AND type = 'conseiller_municipal'
  AND canonical NOT IN (
        'Régine-CURZYDLO-1946-12-29-conseiller_municipal-DIV-2014-03-23',
        'Sylvain-LEBERQUIER-1959-10-30-conseiller_municipal-NC-2014-03-23',
        'Dominique-LEBEY-1960-04-15-conseiller_municipal-NC-2014-03-23',
        'Joël-LETAVERNIER-1965-11-10-conseiller_municipal-NC-2014-03-23',
        'Christiane-BERTIN-1935-08-16-conseiller_municipal-NC-2014-03-23',
        'Régine-CURZYDLO-1946-12-29-conseiller_municipal-DIV-2014-03-23',
        'Séverine-LUCAS-1974-12-17-conseiller_municipal-NC-2014-03-23',
        'Alain-HOUSSAYE-1959-04-26-conseiller_municipal-NC-2014-03-23',
        'Claude-BEAUFILS-1934-02-19-conseiller_municipal-NC-2014-03-23',
        'Catherine-BIENVENU-1956-02-14-conseiller_municipal-NC-2014-03-23',
        'Luc-GOSSI-1959-02-16-conseiller_municipal-NC-2014-03-23',
        'Daniel-PUPIN-1931-09-13-conseiller_municipal-NC-2014-03-23'
    )
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Vauville (14800)' AND category_id = 1)
WHERE commune_nom = 'La Hague' AND dpt = '14' AND type = 'conseiller_municipal'
  AND canonical IN (
        'Régine-CURZYDLO-1946-12-29-conseiller_municipal-DIV-2014-03-23',
        'Sylvain-LEBERQUIER-1959-10-30-conseiller_municipal-NC-2014-03-23',
        'Dominique-LEBEY-1960-04-15-conseiller_municipal-NC-2014-03-23',
        'Joël-LETAVERNIER-1965-11-10-conseiller_municipal-NC-2014-03-23',
        'Christiane-BERTIN-1935-08-16-conseiller_municipal-NC-2014-03-23',
        'Régine-CURZYDLO-1946-12-29-conseiller_municipal-DIV-2014-03-23',
        'Séverine-LUCAS-1974-12-17-conseiller_municipal-NC-2014-03-23',
        'Alain-HOUSSAYE-1959-04-26-conseiller_municipal-NC-2014-03-23',
        'Claude-BEAUFILS-1934-02-19-conseiller_municipal-NC-2014-03-23',
        'Catherine-BIENVENU-1956-02-14-conseiller_municipal-NC-2014-03-23',
        'Luc-GOSSI-1959-02-16-conseiller_municipal-NC-2014-03-23',
        'Daniel-PUPIN-1931-09-13-conseiller_municipal-NC-2014-03-23'
    )
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Herqueville (27430)' AND category_id = 1)
WHERE commune_nom = 'La Hague' AND dpt = '27' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Couvains (50680)' AND category_id = 1)
WHERE commune_nom = 'La Ferté-en-Ouche' AND dpt = '50' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Gauville (80290)' AND category_id = 1)
WHERE commune_nom = 'La Ferté-en-Ouche' AND dpt = '80' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bellefontaine (39400)' AND category_id = 1)
WHERE commune_nom = 'Juvigny les Vallées' AND dpt = '39' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bellefontaine (88370)' AND category_id = 1)
WHERE commune_nom = 'Juvigny les Vallées' AND dpt = '88' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bellefontaine (95270)' AND category_id = 1)
WHERE commune_nom = 'Juvigny les Vallées' AND dpt = '95' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'La Bazoge (72650)' AND category_id = 1)
WHERE commune_nom = 'Juvigny les Vallées' AND dpt = '72' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ingrandes (36300)' AND category_id = 1)
WHERE commune_nom = 'Ingrandes-Le Fresne sur Loire' AND dpt = '36' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ingrandes (86220)' AND category_id = 1)
WHERE commune_nom = 'Ingrandes-Le Fresne sur Loire' AND dpt = '86' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Benoît (04240)' AND category_id = 1)
WHERE commune_nom = 'Groslée-Saint-Benoit' AND dpt = '04' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Benoît (11230)' AND category_id = 1)
WHERE commune_nom = 'Groslée-Saint-Benoit' AND dpt = '11' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Benoît (86280)' AND category_id = 1)
WHERE commune_nom = 'Groslée-Saint-Benoit' AND dpt = '86' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Termes (11330)' AND category_id = 1)
WHERE commune_nom = 'Grandpré' AND dpt = '11' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Termes (48310)' AND category_id = 1)
WHERE commune_nom = 'Grandpré' AND dpt = '48' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Martigny (02500)' AND category_id = 1)
WHERE commune_nom = 'Grandparigny' AND dpt = '02' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Martigny (76880)' AND category_id = 1)
WHERE commune_nom = 'Grandparigny' AND dpt = '76' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Parigny (42120)' AND category_id = 1)
WHERE commune_nom = 'Grandparigny' AND dpt = '42' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Chèvreville (60440)' AND category_id = 1)
WHERE commune_nom = 'Grandparigny' AND dpt = '60' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Goupillières (14210)' AND category_id = 1)
WHERE commune_nom = 'Goupil-Othon' AND dpt = '14' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Goupillières (76570)' AND category_id = 1)
WHERE commune_nom = 'Goupil-Othon' AND dpt = '76' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Goupillières (78770)' AND category_id = 1)
WHERE commune_nom = 'Goupil-Othon' AND dpt = '78' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Quézac (15600)' AND category_id = 1)
WHERE commune_nom = 'Gorges du Tarn Causses' AND dpt = '15' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Montbrun (46160)' AND category_id = 1)
WHERE commune_nom = 'Gorges du Tarn Causses' AND dpt = '46' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Épagny (02290)' AND category_id = 1)
WHERE commune_nom = 'Epagny Metz-Tessy ' AND dpt = '02' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Épagny (21380)' AND category_id = 1)
WHERE commune_nom = 'Epagny Metz-Tessy ' AND dpt = '21' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Batilly (54980)' AND category_id = 1)
WHERE commune_nom = 'Écouché-les-Vallées' AND dpt = '54' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Serans (60240)' AND category_id = 1)
WHERE commune_nom = 'Écouché-les-Vallées' AND dpt = '60' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fontaines (71150)' AND category_id = 1)
WHERE commune_nom = 'Doix lès Fontaines' AND dpt = '71' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Fontaines (89130)' AND category_id = 1)
WHERE commune_nom = 'Doix lès Fontaines' AND dpt = '89' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Villiers-le-Sec (52000)' AND category_id = 1)
WHERE commune_nom = 'Creully sur Seulles' AND dpt = '52' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Villiers-le-Sec (58210)' AND category_id = 1)
WHERE commune_nom = 'Creully sur Seulles' AND dpt = '58' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Villiers-le-Sec (95720)' AND category_id = 1)
WHERE commune_nom = 'Creully sur Seulles' AND dpt = '95' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Avit (16210)' AND category_id = 1)
WHERE commune_nom = 'Couëtron-au-Perche' AND dpt = '16' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Avit (26330)' AND category_id = 1)
WHERE commune_nom = 'Couëtron-au-Perche' AND dpt = '26' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Avit (40090)' AND category_id = 1)
WHERE commune_nom = 'Couëtron-au-Perche' AND dpt = '40' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Avit (47350)' AND category_id = 1)
WHERE commune_nom = 'Couëtron-au-Perche' AND dpt = '47' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Avit (63380)' AND category_id = 1)
WHERE commune_nom = 'Couëtron-au-Perche' AND dpt = '63' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Avit (81110)' AND category_id = 1)
WHERE commune_nom = 'Couëtron-au-Perche' AND dpt = '81' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Oigny (21450)' AND category_id = 1)
WHERE commune_nom = 'Couëtron-au-Perche' AND dpt = '21' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Arville (77890)' AND category_id = 1)
WHERE commune_nom = 'Couëtron-au-Perche' AND dpt = '77' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Noailhac (19500)' AND category_id = 1)
WHERE commune_nom = 'Conques-en-Rouergue' AND dpt = '19' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Noailhac (81490)' AND category_id = 1)
WHERE commune_nom = 'Conques-en-Rouergue' AND dpt = '81' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Malicorne (03600)' AND category_id = 1)
WHERE commune_nom = 'Charny Orée de Puisaye' AND dpt = '03' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Grandchamp (08270)' AND category_id = 1)
WHERE commune_nom = 'Charny Orée de Puisaye' AND dpt = '08' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Grandchamp (52600)' AND category_id = 1)
WHERE commune_nom = 'Charny Orée de Puisaye' AND dpt = '52' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Grandchamp (72610)' AND category_id = 1)
WHERE commune_nom = 'Charny Orée de Puisaye' AND dpt = '72' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Grandchamp (78113)' AND category_id = 1)
WHERE commune_nom = 'Charny Orée de Puisaye' AND dpt = '78' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Charny (21350)' AND category_id = 1)
WHERE commune_nom = 'Charny Orée de Puisaye' AND dpt = '21' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Charny (77410)' AND category_id = 1)
WHERE commune_nom = 'Charny Orée de Puisaye' AND dpt = '77' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Perreux (42120)' AND category_id = 1)
WHERE commune_nom = 'Charny Orée de Puisaye' AND dpt = '42' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaumont (19390)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '19' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaumont (43100)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '43' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Beaumont (74160)' AND category_id = 1)
WHERE commune_nom = 'Beaumont Saint-Cyr' AND dpt = '74' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Isigny-le-Buat (50540)' AND category_id = 1)
WHERE commune_nom = 'ISIGNY LE BUAT Section 01' AND dpt = '50' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Isigny-le-Buat (50540)' AND category_id = 1)
WHERE commune_nom = 'ISIGNY LE BUAT Section 03' AND dpt = '50' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Jean-des-Champs (50320)' AND category_id = 1)
WHERE commune_nom = 'SAINT JEAN DES CHAMPS Section 01' AND dpt = '50' AND type = 'conseiller_municipal'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Ingrandes-Le Fresne sur Loire (49123)' AND category_id = 1)
WHERE commune_nom = 'Ingrandes-Le Fresne sur Loire' AND dpt = '44' AND type = 'conseiller_municipal'
;
UPDATE elected_representative er
    JOIN elected_representative_mandate m ON m.elected_representative_id = er.id
SET comment = 'Attention, commune créée en 2016 par la fusion de Ingrandes (49) et Fresne-sur-Loire (44).'
WHERE m.commune_nom = 'Ingrandes-Le Fresne sur Loire' AND m.dpt = '44' AND m.type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Crépin-Ibouvillers (60149,60790)' AND category_id = 1)
WHERE commune_nom = 'Montherlant' AND dpt = '60' AND type = 'conseiller_municipal'
;
UPDATE elected_representative er
    JOIN elected_representative_mandate m ON m.elected_representative_id = er.id
SET comment = 'Attention, commune fusionnée dans Saint-Crépin-Ibouvillers (60).'
WHERE m.commune_nom = 'Montherlant' AND m.dpt = '60' AND m.type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Tessy-Bocage (50420)' AND category_id = 1)
WHERE commune_nom = 'Tessy-Bocage' AND dpt = '14' AND type = 'conseiller_municipal'
;
UPDATE elected_representative er
    JOIN elected_representative_mandate m ON m.elected_representative_id = er.id
SET comment = 'Attention, fusion en 2018 avec Pont-Farcy (14).'
WHERE m.commune_nom = 'Tessy-Bocage' AND m.dpt = '14' AND m.type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Val Suran (39320)' AND category_id = 1)
WHERE commune_nom = 'Val Suran' AND dpt = '88' AND type = 'conseiller_municipal'
;
UPDATE elected_representative er
    JOIN elected_representative_mandate m ON m.elected_representative_id = er.id
SET comment = 'Attention, commune résultant de la fusion de Bourcia, de Louvenne, de Saint-Julien et de Villechantria le 1er Janvier 2017'
WHERE m.commune_nom = 'Val Suran' AND m.dpt = '88' AND m.type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Vallons-de-l''Erdre (44540)' AND category_id = 1)
WHERE commune_nom = 'Vallons-de-l''Erdre' AND dpt = '49' AND type = 'conseiller_municipal'
;
UPDATE elected_representative er
    JOIN elected_representative_mandate m ON m.elected_representative_id = er.id
SET comment = 'Attention, cette commune est le résultat de la fusion de Bonnœuvre (44), Maumusson (44), Saint-Mars-la-Jaille (44), Saint-Sulpice-des-Landes (44), Vritz (44) et Freigné (49) le 1er janvier 2018.'
WHERE m.commune_nom = 'Vallons-de-l''Erdre' AND m.dpt = '49' AND m.type = 'conseiller_municipal'
;

-- Ajouter les villes manquantes
INSERT INTO elected_representative_zone (name, category_id) VALUES
    ('Aubigny-en-Laonnois (02820)', 1),
    ('Blaincourt-sur-Aube (10500)', 1),
    ('Bouranton (10270)', 1),
    ('Bouy-Luxembourg (10220)', 1),
    ('Braye-sur-Maulne (37330)', 1),
    ('Bréhémont (37130)', 1),
    ('Brienne-la-Vieille (10500)', 1),
    ('Cangey (37530)', 1),
    ('Chauffour-lès-Bailly (10110)', 1),
    ('Le Bono (56400)', 1),
    ('Lion-sur-Mer (14780)', 1),
    ('Maltot (14930)', 1),
    ('Mézidon Vallée d''Auge (14270)', 1),
    ('Montigny (14210)', 1),
    ('Saint-Lucien (76780)', 1),
    ('Saint-Quentin-de-Baron (33750)', 1),
    ('Saint-Selve (33650)', 1),
    ('Sigottier (05700)', 1),
    ('Bournan (37240)', 1),
    ('Le Boulay (37110)', 1),
    ('Etaux (74800)', 1),
    ('Marseille (13)', 1),
    ('Lyon (69)', 1),
    ('Anaa (98760)', 1),
    ('Arutua (98761)', 1),
    ('Bélep (98811)', 1),
    ('Bora-Bora (98730)', 1),
    ('Boulouparis (98812)', 1),
    ('Bourail (98870)', 1),
    ('Canala (98813)', 1),
    ('Dumbéa (98830, 98835, 98837, 98839)', 1),
    ('Faaa (98704)', 1),
    ('Fakarava (98763)', 1),
    ('Fangatau (98765)', 1),
    ('Fatu-Hiva (98740)', 1),
    ('Gambier (98755)', 1),
    ('Hao (98767)', 1),
    ('Hienghène (98815)', 1),
    ('Hikueru (98768)', 1),
    ('Hitiaa O Te Ra (98705)', 1),
    ('Hiva-Oa (98741)', 1),
    ('Houaïlou (98816)', 1),
    ('Huahine (98731)', 1),
    ('Île des Pins (98832)', 1),
    ('Kaala-Gomen (98817)', 1),
    ('Koné (98860)', 1),
    ('Kouaoua (98818)', 1),
    ('Koumac (98850)', 1),
    ('La Foa (98880)', 1),
    ('Lifou (98820, 98884, 98885)', 1),
    ('Mahina (98709)', 1),
    ('Makemo (98769)', 1),
    ('Manihi (98771)', 1),
    ('Maré (98828, 98878)', 1),
    ('Maupiti (98732)', 1),
    ('Miquelon-Langlade (97500)', 1),
    ('Moindou (98819)', 1),
    ('Moorea-Maiao (98728)', 1),
    ('Napuka (98772)', 1),
    ('Nouméa (98800)', 1),
    ('Nuku-Hiva (98742)', 1),
    ('Nukutavake (98773)', 1),
    ('Ouégoa (98821)', 1),
    ('Ouvéa (98814)', 1),
    ('Paea (98711)', 1),
    ('Païta (98890)', 1),
    ('Papara (98712)', 1),
    ('Papeete (98713, 98714)', 1),
    ('Pirae (98716)', 1),
    ('Poindimié (98822)', 1),
    ('Ponérihouen (98823)', 1),
    ('Pouébo (98824)', 1),
    ('Pouembout (98825)', 1),
    ('Poya (98827)', 1),
    ('Pukapuka (98774)', 1),
    ('Punaauia (98718)', 1),
    ('Raivavae (98750)', 1),
    ('Rangiroa (98776)', 1),
    ('Rapa (98751)', 1),
    ('Reao (98779)', 1),
    ('Rimatara (98752)', 1),
    ('Rurutu (98753)', 1),
    ('Saint-Pierre (97500)', 1),
    ('Sarraméa (98880)', 1),
    ('Taha''a (98733)', 1),
    ('Tahuata (98743)', 1),
    ('Taiarapu-Est (98722)', 1),
    ('Taiarapu-Ouest (98722)', 1),
    ('Takaroa (98781)', 1),
    ('Taputapuatea (98735)', 1),
    ('Tatakoto (98783)', 1),
    ('Teva I Uta (98726)', 1),
    ('Thio (98829)', 1),
    ('Touho (98831)', 1),
    ('Tubuai (98754)', 1),
    ('Tumaraa (98735)', 1),
    ('Tureia (98784)', 1),
    ('Ua-Huka (98744)', 1),
    ('Ua-Pou (98745)', 1),
    ('Uturoa (98735)', 1),
    ('Yaté (98834)', 1)
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Etaux (74800)' AND category_id = 1)
WHERE commune_nom = 'Eteaux' AND dpt = '74' AND type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Faaa (98704)' AND category_id = 1)
WHERE commune_nom = 'Faa a' AND dpt = '' AND type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Pukapuka (98774)' AND category_id = 1)
WHERE commune_nom = 'Puka Puka' AND dpt = '' AND type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Taha''a (98733)' AND category_id = 1)
WHERE commune_nom = 'Tahaa' AND dpt = '' AND type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Bélep (98811)' AND category_id = 1)
WHERE commune_nom = 'Belep' AND dpt = '' AND type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Houaïlou (98816)' AND category_id = 1)
WHERE commune_nom = 'Houailou' AND dpt = '' AND type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Île des Pins (98832)' AND category_id = 1)
WHERE commune_nom = 'Ile des Pins' AND dpt = '' AND type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Pianottoli-Caldarello (20131)' AND category_id = 1)
WHERE commune_nom = 'Pianotolli-Caldarello' AND dpt = '' AND type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Schœlcher (97233)' AND category_id = 1)
WHERE commune_nom = 'Schoelcher' AND dpt = '' AND type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = 'Saint-Pierre (97500)' AND category_id = 1)
WHERE commune_nom = 'Saint-Pierre' AND dpt_nom = 'Saint-Pierre-et-Miquelon' AND type = 'conseiller_municipal'
;

UPDATE elected_representative_mandate mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = mandate.epci_nom AND category_id = 2)
WHERE type = 'conseiller_municipal' AND zone_id IS NULL AND dpt = ''
;
-- membre_EPCI
-- pour le rerun et test
-- UPDATE elected_representative_mandate SET zone_id = NULL WHERE type = 'membre_EPCI';
-- SELECT COUNT(id) FROM elected_representative_mandate WHERE zone_id IS NULL AND type = 'membre_EPCI';
ALTER TABLE elected_representative_zone
    ADD epci VARCHAR(255) DEFAULT NULL,
    ADD INDEX zone_epci (epci)
;

UPDATE elected_representative_zone SET name = 'CC des 4 Rivières (EURE)' WHERE name = 'CC des 4 Rivières'
;

UPDATE elected_representative_mandate mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = mandate.epci_nom AND category_id = 2)
WHERE type = 'membre_EPCI'
;
UPDATE elected_representative_mandate mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.epci = mandate.epci AND category_id = 2)
WHERE type = 'membre_EPCI' AND zone_id IS NULL
;

UPDATE elected_representative_zone SET epci = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name, '"', ' '), '?', ' '), '/', ' '), '&', ' '), '-', ' '), ',', ' '), '\'', ' '), '-', ' '), '  ', ' ')
WHERE category_id = 2
;
UPDATE elected_representative_mandate SET epci = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(epci_nom, '"', ' '), '?', ' '), '/', ' '), '&', ' '), '-', ' '), ',', ' '), '\'', ' '), '-', ' '), '  ', ' ')
WHERE epci_nom != ''
;

UPDATE elected_representative_zone SET epci = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(epci, ' EN ', ' '), ' LE ', ' '), ' DE ', ' '), ' DU ', ' '), ' L\'', ' '), ' D\'', ' '), ' LA ', ' '), ' LES ', ' '), ' DES ', ' '), '  ', ' ')
WHERE category_id = 2
;
UPDATE elected_representative_mandate SET epci = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(epci, ' EN ', ' '), ' LE ', ' '), ' LE ', ' '), ' DE ', ' '), ' DU ', ' '), ' L\'', ' '), ' D\'', ' '), ' LA ', ' '), ' LES ', ' '), ' DES ', ' '), '  ', ' ')
WHERE epci_nom != ''
;
UPDATE elected_representative_zone SET epci = REPLACE(epci, '  ', ' ')
WHERE category_id = 2
;
UPDATE elected_representative_mandate SET epci = REPLACE(epci, '  ', ' ')
WHERE type = 'membre_EPCI'
;

UPDATE elected_representative_mandate SET epci = 'CC Arbois, Poligny, Salins, Coeur du Jura' WHERE epci_nom = 'CC DU PAYS DE SALINS LES BAINS       (archivé)';
UPDATE elected_representative_mandate SET epci = 'CC Castelnaudary Lauragais Audois' WHERE epci_nom = 'CC DE CASTELNAUDARY-LAURAGAIS AUDOIS';
UPDATE elected_representative_mandate SET epci = 'CC Vallée des Baux-Alpilles (Cc Vba)' WHERE epci_nom = 'CC DE LA VALLEE DES BAUX ET DES ALPILLES';
UPDATE elected_representative_mandate SET epci = 'CC Hautes Terres' WHERE epci_nom = 'CC HAUTES-TERRES COMMUNAUTE';
UPDATE elected_representative_mandate SET epci = 'CC des Pays de Caldaguès-Aubrac, Pierrefort-Neuvéglise, Planèze, Saint-Flour Margeride' WHERE epci_nom = 'CC Pays Caldaguès-Aubrac, Pierrefort Neuveglise, planèze St Flour Margeride';
UPDATE elected_representative_mandate SET epci = 'CA Rochefort Océan' WHERE epci_nom = 'CA ROCHEFORT OCEAN CARO';
UPDATE elected_representative_mandate SET epci = 'CA Royan Atlantique' WHERE epci_nom = 'CA ROYAN ATLANTIQUE CARA';
UPDATE elected_representative_mandate SET epci = 'CC de l''Ile de Ré' WHERE epci_nom = 'CC ILE DE RE';
UPDATE elected_representative_mandate SET epci = 'CU du Grand Dijon' WHERE epci_nom = 'CU GRAND DIJON (ex COMADI - ex CA GRAND DIJON)';
UPDATE elected_representative_mandate SET epci = 'CC Rives de Saône, Cc Saint Jean de Losne Seurre' WHERE epci_nom = 'CC RIVES DE SAONE ST JEAN DE LOSNE SEURR';
UPDATE elected_representative_mandate SET epci = 'CC Pays de Boussac, Carrefour des Quatre Provinces, Evaux-Les-Bains/Chambon-Sur-Voueize' WHERE epci_nom = 'CC PAYS DE BOUSSAC, CARREFOUR DES 4 PROVINCES, EVAUX-LES-BAINS/CHAMBON-SUR-VOU';
UPDATE elected_representative_mandate SET epci = 'CC de Montbenoit' WHERE epci_nom = 'CC DU CANTON DE MONTBENOÎT';
UPDATE elected_representative_mandate SET epci = 'CC Porte de Dromardèche' WHERE epci_nom = 'CC PORTES DE DROMARDECHE';
UPDATE elected_representative_mandate SET epci = 'CC du Pays de Conches' WHERE epci_nom = 'CC PAYS DE CONCHES EN OUCHE';
UPDATE elected_representative_mandate SET epci = 'CA du Pays de Quimperlé' WHERE epci_nom = 'CA QUIMPERLE COMMUNAUTE';
UPDATE elected_representative_mandate SET epci = 'Toulouse Métropole' WHERE epci_nom = 'CU TOULOUSE METROPOLE';
UPDATE elected_representative_mandate SET epci = 'CC Cagire Garonne Salat' WHERE epci_nom = 'CC CAGIRE GARONNESALAT';
UPDATE elected_representative_mandate SET epci = 'CC du Grand Saint Emilonnais' WHERE epci_nom = 'CC DU GRAND SAINT EMILIONNAIS';
UPDATE elected_representative_mandate SET epci = 'Rennes Métropole' WHERE epci_nom = 'CA DE RENNES METROPOLE';
UPDATE elected_representative_mandate SET epci = 'CC du Pays de Dol et de la Baie du Mont Saint-Michel' WHERE epci_nom = 'CC du PAYS de DOL et de la BAIE DU MONT ST MICHEL';
UPDATE elected_representative_mandate SET epci = 'CC Lyon Saint Exupéry en Dauphiné' WHERE epci_nom = 'CC PORTE DAUPHINOISE DE LYON SAINT-EXUPERY';
UPDATE elected_representative_mandate SET epci = 'Métropole Grenoble-Alpes-Métropole' WHERE epci_nom = 'METROPOLE GRENOBLE ALPES METROPOLE (LA METRO)';
UPDATE elected_representative_mandate SET epci = 'CC de la Station des Rousses-Haut Jura' WHERE epci_nom = 'CC STATION DES ROUSSES HT-JURA';
UPDATE elected_representative_mandate SET epci = 'CC la Grandvallière' WHERE epci_nom = 'CC LA GRANVALLIERE';
UPDATE elected_representative_mandate SET epci = 'CC des Collines du Perche' WHERE epci_nom = 'CC COLLINES DU PERCHE';
UPDATE elected_representative_mandate SET epci = 'CU Saint-Etienne Métropole' WHERE epci_nom = 'CU DE ST-ETIENNE METROPOLE';
UPDATE elected_representative_mandate SET epci = 'CC Charlieu-Belmont' WHERE epci_nom = 'CC DU PAYS DE CHARLIEU BELMONT';
UPDATE elected_representative_mandate SET epci = 'CC Causses et Vallée de la Dordogne' WHERE epci_nom = 'CC CAUSSES ET VALLEES DE LA DORDOGNE (CAUVALDOR)';
UPDATE elected_representative_mandate SET epci = 'CA d''Agen' WHERE epci_nom = 'CA AGGLOMERATION D''AGEN';
UPDATE elected_representative_mandate SET epci = 'CU Angers Loire Métropole' WHERE epci_nom = 'CA ANGERS LOIRE METROPOLE';
UPDATE elected_representative_mandate SET epci = 'CA Saumur Val de Loire' WHERE epci_nom = 'CA SAUMUR LOIRE DEVELOPPEMENT';
UPDATE elected_representative_mandate SET epci = 'CC Meurthe, Mortagne, Moselle' WHERE epci_nom = 'CC MEURTHE, MORTAGNE et MOSELLE';
UPDATE elected_representative_mandate SET epci = 'CC du Territoire de Fresnes en Woëvre' WHERE epci_nom = 'CC DU CANTON DE FRESNES-EN-WOEVRE';
UPDATE elected_representative_mandate SET epci = 'CC de l''Oust À Brocéliande Communauté' WHERE epci_nom = 'CC DE L''OUST A BROCELIANDRE COMMUNAUTE';
UPDATE elected_representative_mandate SET epci = 'CC Questembert Communauté' WHERE epci_nom = 'CC DU PAYS DE QUESTEMBERT';
UPDATE elected_representative_mandate SET epci = 'CC Sarrebourg Moselle Sud' WHERE epci_nom = 'CC DE SARREBOURG - MOSELLE SUD (CCSMS)';
UPDATE elected_representative_mandate SET epci = 'CC Loire et Allier' WHERE epci_nom = 'CC LOIRE ALLIER';
UPDATE elected_representative_mandate SET epci = 'CU de Dunkerque' WHERE epci_nom = 'CU DUNKERQUE GRAND LITTORAL';
UPDATE elected_representative_mandate SET epci = 'CC Flandre Lys' WHERE epci_nom = 'CC FLANDRES LYS';
UPDATE elected_representative_mandate SET epci = 'CA de la Région de Compiègne et de la Basse Automne' WHERE epci_nom = 'CA AGGLOMERATION REGION DE COMPIEGNE ET BASSE AUTOMNE';
UPDATE elected_representative_mandate SET epci = 'CC des Deux Vallées' WHERE epci_nom = 'CC DEUX VALLEES';
UPDATE elected_representative_mandate SET epci = 'CA Creil Sud Oise' WHERE epci_nom = 'CA AGGLOMERATION CREIL SUD OISE';
UPDATE elected_representative_mandate SET epci = 'CA Flers Agglo' WHERE epci_nom = 'CC FLERS AGGLO';
UPDATE elected_representative_mandate SET epci = 'CA du Calaisis' WHERE epci_nom = 'CA GRAND CALAIS TERRES ET MERS';
UPDATE elected_representative_mandate SET epci = 'CC Pays de Nay' WHERE epci_nom = 'CC DU PAYS DE NAY';
UPDATE elected_representative_mandate SET epci = 'CC des Albères et de la Côte Vermeille' WHERE epci_nom = 'CC DES ALBERES, DE LA COTE VERMEILLE ET DE L''ILLIBERIS';
UPDATE elected_representative_mandate SET epci = 'CC du Pays de Barr' WHERE epci_nom = 'CC BARR-BERNSTEIN';
UPDATE elected_representative_mandate SET epci = 'CA Villefranche Beaujolais Saône' WHERE epci_nom = 'CA de VILLEFRANCHE BEAUJOLAIS SAONE';
UPDATE elected_representative_mandate SET epci = 'CC Maison de l''Intercommunalité de Haute Tarentaise' WHERE epci_nom = 'CC MAISON DE L''INTERCO DE HTE TARENTAISE';
UPDATE elected_representative_mandate SET epci = 'CC du Thouarsais' WHERE epci_nom = 'CC DU THOURSAIS';
UPDATE elected_representative_mandate SET epci = 'CC Carmausin-Ségala' WHERE epci_nom = 'CC DU CARMAUSIN ET DU SEGALA CARMAUSIN';
UPDATE elected_representative_mandate SET epci = 'CC Coteaux et Plaines du Pays Lafrançaisain' WHERE epci_nom = 'CC COTEAUX ET PLAINES DU PAYS LAFRANCAISIN';
UPDATE elected_representative_mandate SET epci = 'CC du Pays de Serres en Quercy' WHERE epci_nom = 'CC PAYS DE SERRES';
UPDATE elected_representative_mandate SET epci = 'CC Territoriale Sud-Luberon' WHERE epci_nom = 'CC COMMUNAUTE TERRITORIALE SUD LUBERON (COTELUB)';
UPDATE elected_representative_mandate SET epci = 'CC du Pays de St Gilles-Croix-De-Vie' WHERE epci_nom = 'CC DU PAYS DE SAINT GILLES CROIX DE VIE';
UPDATE elected_representative_mandate SET epci = 'CC des Quatre Rivières (HAUTE-SAONE)' WHERE epci_nom = 'CC DES QUATRE RIVIERES' AND dpt = '70';
UPDATE elected_representative_mandate SET epci = 'CU Tour(S) Plus' WHERE epci_nom = 'CU TOURS';
UPDATE elected_representative_mandate SET epci = 'ViennAgglo' WHERE epci_nom = 'CA DU PAYS VIENNOIS';
UPDATE elected_representative_mandate SET epci = 'CC TERRE LORRAINE DU LONGUYONNAIS' WHERE epci_nom = 'CC DU PAYS DE LONGUYON ET 2 RIVIERES';
UPDATE elected_representative_mandate SET epci = 'CC des Lisières de l''Oise' WHERE epci_nom = 'CC D''ATTICHY';
UPDATE elected_representative_mandate SET epci = 'CC des 4 Rivières (EURE)' WHERE epci_nom = 'CC DES RIVIERES';
UPDATE elected_representative_mandate SET epci = 'CC Coeur d''Ostrevent [c.C.C.O.]' WHERE epci_nom = 'CC COEUR DE L''OSTREVENT';
UPDATE elected_representative_mandate SET epci = 'CC du Val d''Orne' WHERE epci_nom = 'CC  DU VAL D''ORNE';
UPDATE elected_representative_mandate SET epci = 'CA du Pays de Saint-Omer' WHERE epci_nom = 'CA DU PAYS DE SAINT OMER';
UPDATE elected_representative_mandate SET epci = 'CC de la Vallée de Villé' WHERE epci_nom = 'CC CANTON DE VILLE';
UPDATE elected_representative_mandate SET epci = 'CC les Portes de l''Ile de France' WHERE epci_nom = 'CC LES PORTES DE L''ÎLE-DE-FRANCE';
UPDATE elected_representative_mandate SET epci = 'CC Aygues-Ouvèze en Provence (Ccaop)' WHERE epci_nom = 'CC D''AYGUES-OUVEZE EN PROVENCE';
UPDATE elected_representative_mandate SET epci = 'CA Etampois Sud Essonne' WHERE epci_nom = 'CA DE L''ETAMPOIS SUD ESSONNE (CCESE)';
UPDATE elected_representative_mandate SET epci = 'CC Serre-Ponçon' WHERE epci_nom = 'CC DE SERRE-PONCON';

UPDATE elected_representative_mandate mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.name = mandate.epci AND category_id = 2)
WHERE type = 'membre_EPCI' AND zone_id IS NULL
;

INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA Cap Excellence', 'CA CAP EXCELLENCE', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA CAP EXCELLENCE'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA du Centre de la Martinique', 'CA DU CENTRE DE LA MARTINIQUE', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA DU CENTRE DE LA MARTINIQUE'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA du Nord Grande Terre', 'CA DU NORD GRANDE TERRE', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA DU NORD GRANDE TERRE'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA du Pays Nord Martinique', 'CA DU PAYS NORD MARTINIQUE', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA DU PAYS NORD MARTINIQUE'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA du Sud', 'CA DU SUD', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA DU SUD'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA de l''Espace Sud de la Martinique', 'CA ESPACE SUD MARTINIQUE', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA ESPACE SUD MARTINIQUE'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA du Nord Basse-Terre', 'CA NORD BASSE TERRE', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA NORD BASSE TERRE'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC de l''Est Guyanais', 'CC DE L''EST GUYANAIS', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC DE L''EST GUYANAIS'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC de l''Ouest Guyanais', 'CC DE L''OUEST GUYANAIS', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC DE L''OUEST GUYANAIS'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC de Marie-Galante', 'CC DE MARIE-GALANTE', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC DE MARIE-GALANTE'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC des Coteaux du Val d''Arros', 'CC DES COTEAUX DU VAL D''ARROS', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC DES COTEAUX DU VAL D''ARROS'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC des Savanes', 'CC DES SAVANES', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC DES SAVANES';
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC du Sud', 'CC DU SUD', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC DU SUD';
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC Pasquale Paoli', 'CC PASQUALE PAOLI', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC PASQUALE PAOLI';
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC Pyrénées catalanes', 'CC PYRÉNÉES CATALANES', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC PYRÉNÉES CATALANES';
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA du Centre Littoral', 'CC DU CENTRE LITTORAL', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC DU CENTRE LITTORAL'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC Saint-Marcellin Vercors Isère Communauté', 'SAINT MARCELLIN VERCORS ISERE COMMUNAUTE', 2)
    ON DUPLICATE KEY UPDATE epci = 'SAINT MARCELLIN VERCORS ISERE COMMUNAUTE'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC de Brioude Sud Auvergne', 'CC DE BRIOUDE SUD AUVERGNE', 2)
    ON DUPLICATE KEY UPDATE epci = '';
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC Bresse Nord Intercom', 'CC BRESSE NORD INTERCOM', 2)
    ON DUPLICATE KEY UPDATE epci = '';
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA Grand Sud Caraïbe', 'CA DU SUD BASSE TERRE', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA DU SUD BASSE TERRE'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA La Riviera du Levant', 'CC du SUD-EST Grande Terre', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC du SUD-EST Grande Terre'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA CIVIS', 'CA CIVIS', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA CIVIS'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA Intercommunale de la Réunion Est (CIREST)', 'CA INTERCOMMUNALE REUNION EST (CIREST)', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA INTERCOMMUNALE REUNION EST (CIREST)'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA Territoire de la Côte Ouest (TCO)', 'CA TERRITOIRE DE LA COTE OUEST (TCO)', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA TERRITOIRE DE LA COTE OUEST (TCO)'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA Intercommunale du Nord de la Réunion (CINOR)', 'CA INTERCOMMUNALE NORD REUNION (CINOR)', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA INTERCOMMUNALE NORD REUNION (CINOR)'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CA Dembeni-Mamoudzou', 'CA DE DEMEBENI MAMOUDZOU (CADEMA)', 2)
    ON DUPLICATE KEY UPDATE epci = 'CA DE DEMEBENI MAMOUDZOU (CADEMA)'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC du Nord de Mayotte', 'CC DE NORD DE MAYOTTE', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC DE NORD DE MAYOTTE'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC Sundgau', 'CC SUNDGAU', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC SUNDGAU'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC de Petite-Terre', 'CC DE PETITE TERRE', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC DE PETITE TERRE'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC des Îles Marquises', 'CC CODIM - CC DES ILES MARQUISES', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC CODIM - CC DES ILES MARQUISES'
;
INSERT INTO elected_representative_zone (name, epci, category_id)
    VALUES ('CC de Havai', 'CC HAVA''I', 2)
    ON DUPLICATE KEY UPDATE epci = 'CC HAVA''I'
;

UPDATE elected_representative_mandate mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE zone.epci = mandate.epci_nom AND category_id = 2)
WHERE type = 'membre_EPCI' AND zone_id IS NULL
;
-- conseiller_departemental
UPDATE elected_representative_mandate
SET dpt_nom = 'Corse-du-Sud' WHERE dpt_nom = 'Corse sud'
;
UPDATE elected_representative_mandate
SET dpt_nom = 'Indre-et-Loire' WHERE dpt_nom = 'Indre et loire'
;
UPDATE elected_representative_mandate
SET dpt_nom = 'Lot-et-Garonne' WHERE dpt_nom = 'Lot et garonne'
;
UPDATE elected_representative_mandate
SET dpt_nom = 'Maine-et-Loire' WHERE dpt_nom = 'Maine et loire'
;
UPDATE elected_representative_mandate
SET dpt_nom = 'Saône-et-Loire' WHERE dpt_nom = 'Saone et loire'
;
UPDATE elected_representative_mandate
SET dpt_nom = 'Seine-Maritime' WHERE dpt_nom = 'Seine maritime'
;
UPDATE elected_representative_mandate
SET dpt_nom = 'Seine-et-Marne' WHERE dpt_nom = 'Seine et marne'
;
UPDATE elected_representative_mandate
SET dpt_nom = 'Hauts-de-Seine' WHERE dpt_nom = 'Hauts de seine'
;
UPDATE elected_representative_mandate
SET dpt_nom = 'Saint-Pierre-et-Miquelon', circo_legis_nom = '1ère circonscription' WHERE dpt_nom = 'Saint pierre et miquelon'
;
UPDATE elected_representative_mandate
SET dpt_nom = 'La Réunion' WHERE dpt_nom = 'La reunion'
;
UPDATE elected_representative_mandate
SET dpt_nom = 'Saint-Barthélemy et Saint-Martin' WHERE dpt_nom = 'Saint-martin/saint-barthelemy'
;
UPDATE elected_representative_mandate
SET dpt_nom = 'Français établis hors de France' WHERE dpt_nom = 'Francais de l''etranger'
;
UPDATE elected_representative_mandate
SET zone_id = (
    SELECT id
    FROM elected_representative_zone zone
    WHERE (name LIKE CONCAT(dpt_nom, ' (%')
        OR name LIKE CONCAT(REPLACE(dpt_nom, ' ', '-'), ' (%'))
      AND category_id = 3)
WHERE type = 'conseiller_departemental'
;
-- conseiller_regional
UPDATE elected_representative_mandate
SET region_nom = 'Île-de-France' WHERE region_nom = 'Ile de france'
;
UPDATE elected_representative_mandate
SET region_nom = 'Centre-Val de Loire' WHERE region_nom = 'Centre'
;
UPDATE elected_representative_mandate
SET region_nom = 'Pays de la Loire' WHERE region_nom = 'Pays de loire'
;
UPDATE elected_representative_mandate
SET region_nom = 'Nouvelle-Aquitaine' WHERE region_nom = 'Aquitaine'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE name = region_nom AND category_id = 4)
WHERE type = 'conseiller_regional'
;
-- depute
UPDATE elected_representative_mandate
SET dpt_nom = 'Réunion' WHERE dpt_nom = 'La Réunion';
UPDATE elected_representative_mandate
SET zone_id = (
    SELECT id
    FROM elected_representative_zone zone
    WHERE (name LIKE CONCAT(dpt_nom, ', ', circo_legis_nom, '%')
        OR name LIKE CONCAT(REPLACE(dpt_nom, ' ', '-'), ', ', circo_legis_nom, '%'))
      AND category_id = 5)
WHERE type = 'depute'
;
-- membre_assemblee_corse
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE name = 'Corse' AND category_id = 6)
WHERE type = 'membre_assemblee_corse'
;
-- senateur
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE name = dpt_nom AND category_id = 3)
WHERE type = 'senateur'
;
UPDATE elected_representative_mandate
SET zone_id = (SELECT id FROM elected_representative_zone zone WHERE name = 'Francais de l''etranger' AND category_id = 7)
WHERE type = 'senateur'
;
-- Etape 3d: fixer les noms des nuances politiques
UPDATE elected_representative_mandate SET political_affiliation = 'DVG' WHERE political_affiliation = 'LDVG';
UPDATE elected_representative_mandate SET political_affiliation = 'DIV' WHERE political_affiliation = 'LDIV';
UPDATE elected_representative_mandate SET political_affiliation = 'DVD' WHERE political_affiliation = 'LDVD';
UPDATE elected_representative_mandate SET political_affiliation = 'DIV' WHERE political_affiliation = 'AUT';
UPDATE elected_representative_mandate SET political_affiliation = 'UMP' WHERE political_affiliation = 'LUMP';
UPDATE elected_representative_mandate SET political_affiliation = 'UG' WHERE political_affiliation = 'LUG';
UPDATE elected_representative_mandate SET political_affiliation = 'FG' WHERE political_affiliation = 'LFG';
UPDATE elected_representative_mandate SET political_affiliation = 'UC' WHERE political_affiliation = 'LUC';
UPDATE elected_representative_mandate SET political_affiliation = 'FN' WHERE political_affiliation = 'LFN';
UPDATE elected_representative_mandate SET political_affiliation = 'SOC' WHERE political_affiliation = 'LSOC';
UPDATE elected_representative_mandate SET political_affiliation = 'EXG' WHERE political_affiliation = 'LEXG';
UPDATE elected_representative_mandate SET political_affiliation = 'UDI' WHERE political_affiliation = 'LUDI';
UPDATE elected_representative_mandate SET political_affiliation = 'NC' WHERE political_affiliation = 'M-NC';
UPDATE elected_representative_mandate SET political_affiliation = 'UDF' WHERE political_affiliation = 'UDFD';
UPDATE elected_representative_mandate SET political_affiliation = 'UD' WHERE political_affiliation = 'LUD';
UPDATE elected_representative_mandate SET political_affiliation = 'VEC' WHERE political_affiliation = 'LVEC';
UPDATE elected_representative_mandate SET political_affiliation = 'NC' WHERE political_affiliation = 'NCE';
UPDATE elected_representative_mandate SET political_affiliation = 'UC' WHERE political_affiliation = 'CEN';
UPDATE elected_representative_mandate SET political_affiliation = 'DVD' WHERE political_affiliation = 'PREP';
UPDATE elected_representative_mandate SET political_affiliation = 'MDM' WHERE political_affiliation = 'LMDM';
UPDATE elected_representative_mandate SET political_affiliation = 'COM' WHERE political_affiliation = 'LCOM';
UPDATE elected_representative_mandate SET political_affiliation = 'PG' WHERE political_affiliation = 'LPG';
UPDATE elected_representative_mandate SET political_affiliation = 'EXD' WHERE political_affiliation = 'LEXD';
UPDATE elected_representative_mandate SET political_affiliation = 'MD' WHERE political_affiliation = 'MODM';
UPDATE elected_representative_mandate SET political_affiliation = 'PRG' WHERE political_affiliation = 'RDG';
-- Etape 4: remplissage de la table `elected_representative_political_function`
-- Etape 4a: faire `elected_representative_id` et `mandate_id` nullable pour faciliter la migration
ALTER TABLE elected_representative_political_function
    CHANGE elected_representative_id elected_representative_id INT DEFAULT NULL,
    CHANGE mandate_id mandate_id INT DEFAULT NULL,
    ADD canonical_mandate VARCHAR(255) DEFAULT NULL,
    ADD INDEX pf_canonical_mandate (canonical_mandate)
;
-- Etape 4b: remplir `elected_representative_political_function`
INSERT INTO elected_representative_political_function (
    name,
    on_going,
    begin_at,
    canonical_mandate
)
SELECT
    nom_fonction,
    1,
    date_debut_fonction,
    canonical_mandate
FROM elected_representatives_register err
;
-- Etape 4c: faire une liaison avec l'élu et le mandat
UPDATE elected_representative_political_function AS pf
    INNER JOIN elected_representative_mandate AS mandate
    ON mandate.canonical = pf.canonical_mandate
SET pf.mandate_id = mandate.id, pf.elected_representative_id = mandate.elected_representative_id
;
-- Etape 4d: changer les noms des fonctions
UPDATE elected_representative_political_function SET name = 'mayor' WHERE name = 'Maire';
UPDATE elected_representative_political_function SET name = 'deputy_mayor' WHERE name = 'Maire délégué';
UPDATE elected_representative_political_function SET clarification = name, name = 'mayor_assistant' WHERE name LIKE '% adjoint au maire%';
UPDATE elected_representative_political_function SET name = 'president_of_regional_council' WHERE name = 'Président du conseil régional';
UPDATE elected_representative_political_function SET clarification = name, name = 'vice_president_of_regional_council' WHERE name LIKE '% vice-président du conseil régional%';
UPDATE elected_representative_political_function SET name = 'president_of_departmental_council' WHERE name = 'Président du conseil départemental';
UPDATE elected_representative_political_function SET clarification = name, name = 'vice_president_of_departmental_council' WHERE name LIKE '%vice-président du conseil départemental%';
UPDATE elected_representative_political_function SET clarification = name, name = 'secretary' WHERE name LIKE '% Secrétaire%';
UPDATE elected_representative_political_function SET name = 'quaestor' WHERE name = 'Questeur';
UPDATE elected_representative_political_function SET name = 'president_of_national_assembly' WHERE name = 'Président de Assemblée nationale';
UPDATE elected_representative_political_function SET name = 'president_of_commission' WHERE name = 'Président de commission';
UPDATE elected_representative_political_function SET name = 'president_of_group' WHERE name = 'Président de groupe';
UPDATE elected_representative_political_function SET name = 'president_of_epci' WHERE name = 'Président';
UPDATE elected_representative_political_function SET name = 'vice_president_of_epci' WHERE name = 'Vice-président d\'EPCI';
UPDATE elected_representative_political_function SET name = 'deputy_vice_president_of_departmental_council' WHERE name = 'Vice-président délégué du conseil départemental';
UPDATE elected_representative_political_function SET name = 'other_member_of_standing_committee' WHERE name = 'Autre membre commission permanente';
UPDATE elected_representative_political_function SET name = 'other_member' WHERE name = 'Autre membre';
UPDATE elected_representative_political_function SET name = 'no_name' WHERE name = '' OR name IS NULL;
-- Etape 5: exécuter la commande Symfony : bin/console app:elected-representative:complete-migration
-- Classe de la commande : RestructureElectedRepresentativesRegisterCommand
-- Pour vérifier que la commande est bien passée pour les mandats, exécuter un SQL (doit être 0) :
SELECT COUNT(er.id) AS er_nb FROM elected_representative er
    LEFT JOIN
    (
        SELECT COUNT(*) AS nb, elected_representative_id
        FROM elected_representative_mandate
        WHERE `number` = 1
        GROUP BY elected_representative_id
        HAVING nb > 1
    ) mandate ON er.id = mandate.elected_representative_id
WHERE mandate.nb > 1
;
-- Pour verifier que la reprise est bien passée au niveau de nombre des données importées, ces valeurs doivent être égales
SELECT COUNT(id) FROM elected_representatives_register
;
SELECT COUNT(id) FROM elected_representative_political_function
;
-- Pour vérifier pour quels types de mandat des zones n'ont pas été associés (abscence d'un zone pour 'euro_depute' est normal) :
SELECT DISTINCT type FROM elected_representative_mandate WHERE zone_id IS NULL;
-- pour savoir quels mandats manquent de zone EPCI et lesquels :
SELECT COUNT(id) FROM elected_representative_mandate WHERE type = 'membre_EPCI' AND zone_id IS NULL;
SELECT COUNT(DISTINCT epci_nom) FROM elected_representative_mandate WHERE type = 'membre_EPCI' AND zone_id IS NULL;
SELECT DISTINCT epci_nom FROM elected_representative_mandate WHERE type = 'membre_EPCI' AND zone_id IS NULL;
-- pour savoir quels mandats manquent de zone City et lesquels :
SELECT COUNT(id) FROM elected_representative_mandate WHERE type = 'conseiller_municipal' AND (commune_nom IS NULL OR commune_nom = '');
SELECT COUNT(id) FROM elected_representative_mandate WHERE type = 'conseiller_municipal' AND zone_id IS NULL;
SELECT COUNT(DISTINCT ville) FROM elected_representative_mandate WHERE type = 'conseiller_municipal' AND zone_id IS NULL;
-- avec dpt
SELECT DISTINCT commune_nom, GROUP_CONCAT(DISTINCT dpt) AS arr_dpt
FROM elected_representative_mandate
WHERE type = 'conseiller_municipal' AND zone_id IS NULL
GROUP BY commune_nom
HAVING arr_dpt != ''
;
-- sans dpt
SELECT DISTINCT commune_nom, GROUP_CONCAT(DISTINCT dpt_nom) AS dpt_noms, GROUP_CONCAT(DISTINCT dpt) AS arr_dpt
FROM elected_representative_mandate
WHERE type = 'conseiller_municipal' AND zone_id IS NULL
GROUP BY commune_nom
HAVING arr_dpt = ''
;
SELECT *
FROM elected_representative_mandate
WHERE type = 'conseiller_municipal' AND zone_id IS NULL AND dpt = ''
;
-- Pour avoir le nombre des fonctions sans nom, doit être 0 :
SELECT COUNT(id) FROM elected_representative_political_function WHERE name IS NULL OR name = '';
SELECT COUNT(id) FROM elected_representatives_register WHERE (nom_fonction IS NULL OR nom_fonction = '') AND date_debut_fonction IS NOT NULL;
-- Pour savoir les noms des fonctions
SELECT DISTINCT name FROM elected_representative_political_function
;
-- Etape 6: on supprime les tables et les champs temporaires
ALTER TABLE elected_representative DROP canonical
;
-- on ne supprime pas tout de suite sertaines colonnes, elles peuvent être utiles pour l'association avec les tags référent
ALTER TABLE elected_representative_mandate
--    DROP dpt,
--    DROP dpt_nom,
    DROP epci_nom,
    DROP commune_nom,
--    DROP region_nom,
    DROP circo_legis_nom,
    DROP circo_legis_code,
    DROP canonical,
    DROP ville,
    DROP epci
;
ALTER TABLE elected_representatives_register
    DROP canonical_adherent,
    DROP canonical_mandate
;
ALTER TABLE elected_representative_political_function
    CHANGE elected_representative_id elected_representative_id INT NOT NULL,
    CHANGE mandate_id mandate_id INT NOT NULL,
    DROP canonical_mandate
;
ALTER TABLE elected_representative_zone
    DROP epci
;
ALTER TABLE elected_representative_mandate
    DROP INDEX er_mandate_type
;
ALTER TABLE elected_representative_zone
    DROP INDEX er_zone_name
;
DROP TABLE temp_adherents_canonical
;
-- Pour supprimer toutes les données importées par la migration
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE elected_representative_social_network_link;
TRUNCATE TABLE elected_representative_label;
TRUNCATE TABLE elected_representative_sponsorship;
TRUNCATE TABLE elected_representative_political_function;
TRUNCATE TABLE elected_representative_mandate;
TRUNCATE TABLE elected_representative;
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE enmarche.elected_representative_social_network_link AUTO_INCREMENT = 0;
ALTER TABLE enmarche.elected_representative_label AUTO_INCREMENT = 0;
ALTER TABLE enmarche.elected_representative_sponsorship AUTO_INCREMENT = 0;
ALTER TABLE enmarche.elected_representative_political_function AUTO_INCREMENT = 0;
ALTER TABLE enmarche.elected_representative_mandate AUTO_INCREMENT = 0;
ALTER TABLE enmarche.elected_representative AUTO_INCREMENT = 0;
