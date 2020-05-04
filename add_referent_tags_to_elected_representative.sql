-- Update referent tag name
UPDATE referent_tags SET name = 'Alpes-de-Haute-Provence (04)' WHERE name = 'Alpes-de-Hautes-Provence (04)'
;

-- COMMUNES
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN cities ON zone.name = CONCAT(cities.name,' (',cities.postal_codes,')')
    LEFT JOIN department ON cities.department_id = department.id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name
WHERE tag.type = 'department' AND zone.category_id = 1;
-- cas de Paris
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN referent_tags tag ON zone.name = tag.name
WHERE zone.category_id = 1 AND zone.name LIKE 'Paris %'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 1 AND zone.name LIKE 'Paris %' AND tag.name = 'Paris' AND tag.type = 'department'
;
-- les cas qui restent : recherche d'un referent tag (code) par 2 ou 3 premiers chiffre du CP de ville
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN referent_tags tag ON (tag.code = substr(zone.name,instr(zone.name,'(') + 1, 2) OR tag.code = substr(zone.name,instr(zone.name,'(') + 1, 3))
    LEFT JOIN elected_representative_zone_referent_tag zt ON zt.elected_representative_zone_id = zone.id
WHERE tag.type = 'department' AND zone.category_id = 1
  AND zt.referent_tag_id IS NULL
;
-- vérification
SELECT * FROM elected_representative_zone zone
  LEFT JOIN elected_representative_zone_referent_tag zone_tag ON zone.id = zone_tag.elected_representative_zone_id
WHERE zone_tag.referent_tag_id IS NULL AND zone.category_id = 1
;

-- EPCI
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN epci ON (zone.name = epci.name OR zone.name = CONCAT(epci.name, ' (', epci.department_name , ')'))
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = epci.department_name
WHERE zone.category_id = 2 AND tag.id IS NOT NULL
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA Cap Excellence' AND tag.name = 'Guadeloupe (971)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA du Centre de la Martinique' AND tag.name = 'Martinique (972)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA du Nord Grande Terre' AND tag.name = 'Guadeloupe (971)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA du Pays Nord Martinique' AND tag.name = 'Martinique (972)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA du Sud' AND tag.name = 'La Réunion (974)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA de l''Espace Sud de la Martinique' AND tag.name = 'Martinique (972)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA du Nord Basse-Terre' AND tag.name = 'Guadeloupe (971)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC de l''Est Guyanais' AND tag.name = 'Guyane (973)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC de l''Ouest Guyanais' AND tag.name = 'Guyane (973)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC de Marie-Galante' AND tag.name = 'Guadeloupe (971)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC des Coteaux du Val d''Arros' AND tag.name = 'Hautes-Pyrénées (65)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC des Savanes' AND tag.name = 'Guyane (973)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC du Sud' AND tag.name = 'Mayotte (976)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC Pasquale Paoli' AND tag.name = 'Haute-Corse (2B)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC Pyrénées catalanes' AND tag.name = 'Pyrénées-Orientales (66)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA du Centre Littoral' AND tag.name = 'Guyane (973)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC Saint-Marcellin Vercors Isère Communauté' AND tag.name = 'Isère (38)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC de Brioude Sud Auvergne' AND tag.name = 'Haute-Loire (43)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC Bresse Nord Intercom' AND tag.name = 'Saône-et-Loire (71)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA Grand Sud Caraïbe' AND tag.name = 'Guadeloupe (971)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA La Riviera du Levant' AND tag.name = 'Guadeloupe (971)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA CIVIS' AND tag.name = 'La Réunion (974)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA Intercommunale de la Réunion Est (CIREST)' AND tag.name = 'La Réunion (974)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA Territoire de la Côte Ouest (TCO)' AND tag.name = 'La Réunion (974)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA Intercommunale du Nord de la Réunion (CINOR)' AND tag.name = 'La Réunion (974)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CA Dembeni-Mamoudzou' AND tag.name = 'Mayotte (976)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC du Nord de Mayotte' AND tag.name = 'Mayotte (976)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC Sundgau' AND tag.name = 'Haut-Rhin (68)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC de Petite-Terre' AND tag.name = 'Mayotte (976)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC des Îles Marquises' AND tag.name = 'Polynésie Française (987)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 2 AND zone.name = 'CC de Havai' AND tag.name = 'Polynésie Française (987)'
;
-- vérification
SELECT * FROM elected_representative_zone zone
    LEFT JOIN elected_representative_zone_referent_tag zone_tag ON zone.id = zone_tag.elected_representative_zone_id
WHERE zone_tag.referent_tag_id IS NULL AND zone.category_id = 2
;

-- DEPARTEMENTS
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN referent_tags tag ON (LEFT(tag.name,LOCATE(' (',tag.name) - 1) = LEFT(zone.name,LOCATE(' (',zone.name) - 1) AND zone.category_id = 3)
WHERE tag.type = 'department'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE tag.type = 'department' AND tag.name = 'Polynésie Française (987)' AND zone.name = 'Wallis et Futuna (986)'
;
-- Paris comme départment
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN referent_tags tag ON zone.name = CONCAT(tag.name, ' (75)')
WHERE tag.type = 'department' AND tag.name = 'Paris'
;
-- vérification, il doit restait que Terres australes et antarctiques françaises (984) et Île de Clipperton (989)
SELECT * FROM elected_representative_zone zone
    LEFT JOIN elected_representative_zone_referent_tag zone_tag ON zone.id = zone_tag.elected_representative_zone_id
WHERE zone_tag.referent_tag_id IS NULL AND zone.category_id = 3
;

-- REGIONS
-- les cas où le nom du région est le même que celui du dpt
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN referent_tags tag ON (tag.name LIKE CONCAT(zone.name, ' (%)') AND zone.category_id = 4)
WHERE tag.type = 'department'
;
-- les cas par région
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4 AND zone.name = 'Île-de-France'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4 AND zone.name = 'Centre-Val de Loire'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4 AND zone.name = 'Bourgogne-Franche-Comté'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4 AND zone.name = 'Normandie'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4 AND zone.name = 'Hauts-de-France'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4 AND zone.name = 'Grand Est'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4 AND zone.name = 'Pays de la Loire'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4 AND zone.name = 'Bretagne'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4 AND zone.name = 'Nouvelle-Aquitaine'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4 AND zone.name = 'Occitanie'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4 AND zone.name = 'Auvergne-Rhône-Alpes'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4
  AND zone.name = 'Provence-Alpes-Côte d''Azur'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN region ON zone.name = region.name
    LEFT JOIN department ON region.id = department.region_id
    LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
WHERE zone.category_id = 4
  AND zone.name = 'Collectivités d''Outre-Mer'
  AND department.name NOT IN ('Wallis et Futuna', 'Île de Clipperton', 'Terres australes et antarctiques françaises')
;
-- vérification, que Corse doive rester
SELECT * FROM elected_representative_zone zone
    LEFT JOIN elected_representative_zone_referent_tag zone_tag ON zone.id = zone_tag.elected_representative_zone_id
WHERE zone_tag.referent_tag_id IS NULL AND zone.category_id = 4
;
-- aide
-- SELECT zone.name, department.name, tag.name FROM elected_representative_zone zone
--     LEFT JOIN region ON zone.name = region.name
--     LEFT JOIN department ON region.id = department.region_id
--     LEFT JOIN referent_tags tag ON LEFT(tag.name,LOCATE(' (',tag.name) - 1) = department.name OR tag.name = department.name
-- WHERE zone.category_id = 4 AND zone.name = 'Collectivités d''Outre-Mer'
-- ;
-- DELETE tag FROM elected_representative_zone_referent_tag tag
--     INNER JOIN elected_representative_zone zone ON tag.elected_representative_zone_id = zone.id
-- WHERE zone.category_id = 4
-- ;

-- CIRCONSCRIPTIONS
INSERT IGNORE INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN referent_tags tag ON (
            tag.name = LEFT(zone.name,LOCATE(',',zone.name) - 1)
        OR LEFT(tag.name,LOCATE(' (',tag.name) - 1) = LEFT(zone.name,LOCATE(',',zone.name) - 1)
    )
WHERE tag.type = 'department' AND zone.category_id = 5
;
-- pour referent tag Paris avec l'arrondissement
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN referent_tags tag ON zone.name LIKE CONCAT('Paris, %(',LEFT(tag.code, LENGTH(tag.code) -3),'-',RIGHT(tag.code, 2), ')')
WHERE zone.category_id = 5 AND zone.name LIKE 'Paris, %'
;
-- cas des FDE
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name LIKE 'Français établis hors de France, %' AND tag.code = 'FOF'
;
-- autres cas
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name LIKE 'Réunion, %' AND tag.name = 'La Réunion (974)'
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5
  AND zone.name = 'Saint-Barthélemy et Saint-Martin, 1ère circonscription (977-01)'
  AND tag.name IN ('Saint-Martin (971)', 'Saint-Barthelemy (971)')
;
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Wallis-et-Futuna, 1ère circonscription (986-01)' AND tag.name = 'Polynésie Française (987)'
;
-- pour les députés
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN referent_tags tag ON tag.name = zone.name
WHERE tag.type = 'district'
;
-- vérification
SELECT * FROM elected_representative_zone zone
    LEFT JOIN elected_representative_zone_referent_tag zone_tag ON zone.id = zone_tag.elected_representative_zone_id
WHERE zone_tag.referent_tag_id IS NULL AND zone.category_id = 5
;
-- aide
-- SELECT COUNT(id) FROM elected_representative_zone
-- WHERE category_id = 5
-- ;
-- SELECT * FROM elected_representative_zone_referent_tag tag
--     INNER JOIN elected_representative_zone zone ON tag.elected_representative_zone_id = zone.id
-- WHERE zone.category_id = 5
-- ;
-- DELETE tag FROM elected_representative_zone_referent_tag tag
--     INNER JOIN elected_representative_zone zone ON tag.elected_representative_zone_id = zone.id
-- WHERE zone.category_id = 5
-- ;

-- CORSE
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN referent_tags tag ON zone.name LIKE CONCAT('%',tag.name,'%')
WHERE tag.name = 'Corse' AND zone.name IN (
       'CC de l''Ouest Corse',
       'CC du Cap Corse',
       'CC du Centre Corse',
       'CC du Sud Corse',
       'Corse',
       'Corse-du-Sud, 1ère circonscription (2A-01)',
       'Corse-du-Sud, 2ème circonscription (2A-02)',
       'Corsept (44560)',
       'Corseul (22130)',
       'Haute-Corse, 1ère circonscription (2B-01)',
       'Haute-Corse, 2ème circonscription (2B-02)'
    )
;
-- vérification
SELECT * FROM elected_representative_zone zone
    LEFT JOIN elected_representative_zone_referent_tag zone_tag ON zone.id = zone_tag.elected_representative_zone_id
WHERE zone_tag.referent_tag_id IS NULL AND zone.category_id = 6
;
SELECT * FROM elected_representative_zone zone
    LEFT JOIN elected_representative_zone_referent_tag zone_tag ON zone.id = zone_tag.elected_representative_zone_id
WHERE zone_tag.referent_tag_id IS NULL AND zone.category_id = 4
;

-- FDE
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone
    LEFT JOIN referent_tags tag ON tag.name = zone.name
WHERE tag.code = 'FOF'
;
-- vérification
SELECT * FROM elected_representative_zone zone
    LEFT JOIN elected_representative_zone_referent_tag zone_tag ON zone.id = zone_tag.elected_representative_zone_id
WHERE zone_tag.referent_tag_id IS NULL AND zone.category_id = 7
;

-- Pour supprimer toutes les données importées par la migration
TRUNCATE TABLE elected_representative_zone_referent_tag
;

