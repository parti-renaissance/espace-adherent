-- DELETE incorrect link between elected_representative_zone and referent_tags for CIRCO
DELETE elt FROM elected_representative_zone_referent_tag elt
    LEFT JOIN elected_representative_zone zone ON elt.elected_representative_zone_id = zone.id
    LEFT JOIN referent_tags tags ON tags.id = elt.referent_tag_id
WHERE zone.name LIKE '%Paris% circo%' AND tags.`type` NOT IN ('department', 'district')
;

-- INSERT correct link between elected_representative_zone and referent_tags for CIRCO
INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 1ère circonscription (75-01)'
  AND tag.code = '75009'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 2ème circonscription (75-02)'
  AND tag.code = '75007'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 3ème circonscription (75-03)'
  AND tag.code = '75017'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 4ème circonscription (75-04)'
  AND tag.code = '75017'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 5ème circonscription (75-05)'
  AND tag.code = '75010'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 6ème circonscription (75-06)'
  AND tag.code = '75011'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 7ème circonscription (75-07)'
  AND tag.code IN ('75001', '75002', '75003', '75004')
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 8ème circonscription (75-08)'
  AND tag.code = '75012'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 9ème circonscription (75-09)'
  AND tag.code = '75013'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 10ème circonscription (75-10)'
  AND tag.code = '75014'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 11ème circonscription (75-11)'
  AND tag.code = '75014'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 12ème circonscription (75-12)'
  AND tag.code = '75015'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 13ème circonscription (75-13)'
  AND tag.code = '75015'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 14ème circonscription (75-14)'
  AND tag.code IN ('75016', '75116')
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 15ème circonscription (75-15)'
  AND tag.code = '75020'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 16ème circonscription (75-16)'
  AND tag.code = '75019'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 17ème circonscription (75-17)'
  AND tag.code = '75018'
;

INSERT INTO elected_representative_zone_referent_tag (elected_representative_zone_id, referent_tag_id)
SELECT zone.id, tag.id FROM elected_representative_zone zone, referent_tags tag
WHERE zone.category_id = 5 AND zone.name = 'Paris, 18ème circonscription (75-18)'
  AND tag.code = '75018'
;
