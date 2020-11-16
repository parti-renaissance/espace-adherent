-- 00-custom.csv
SELECT code, name
FROM enmarche.geo_custom_zone
;

-- 01-foreign-district.csv
SELECT gfd.code, gfd.name, gfd.number, gcz.code AS code_custom
FROM enmarche.geo_foreign_district gfd
    INNER JOIN enmarche.geo_custom_zone gcz on gcz.id = gfd.custom_zone_id
;

-- 02-consular-district.csv
SELECT gcd.code, gcd.name, gcd.number, gcd.cities, gfd.code AS code_foreign_district
FROM enmarche.geo_consular_district gcd
    INNER JOIN enmarche.geo_foreign_district gfd on gfd.id = gcd.foreign_district_id
;

-- 03-country.csv
SELECT gc.code, gc.name, gfd.code AS code_foreign_district
FROM enmarche.geo_country gc
    LEFT JOIN enmarche.geo_foreign_district gfd on gfd.id = gc.foreign_district_id
;

-- 04-region.csv
SELECT gr.code, gr.name, gc.code AS code_country
FROM enmarche.geo_region gr INNER JOIN enmarche.geo_country gc ON gc.id = gr.country_id
;

-- 05-department.csv
SELECT gd.code, gd.name, gr.code AS code_region
FROM enmarche.geo_department gd
    INNER JOIN enmarche.geo_region gr ON gr.id = gd.region_id
;

-- 06-districts.csv
SELECT gd.code, gd.name, gd.number, gdep.code AS code_department
FROM enmarche.geo_district gd
    INNER JOIN enmarche.geo_department gdep ON gdep.id = gd.department_id
;

-- 07-canton.csv
SELECT gc.code, gc.name, gd.code AS code_department
FROM enmarche.geo_canton gc
    INNER JOIN enmarche.geo_department gd ON gd.id = gc.department_id
WHERE gd.code IN ('06', '13', '59', '69', '75', '76', '77', '92', '93', '94')
;

-- 08-city-community.csv
SELECT gcc.code, gcc.name, GROUP_CONCAT(gd.code) AS code_department
FROM enmarche.geo_city_community gcc
    INNER JOIN enmarche.geo_city_community_department gccd ON gccd.city_community_id = gcc.id
    INNER JOIN enmarche.geo_department gd ON gd.id = gccd.department_id
WHERE gd.code IN ('06', '13', '59', '69', '75', '76', '77', '92', '93', '94')
GROUP BY 1, 2
;

-- 09-city.csv
SELECT gc.code, gc.name, gc.postal_code, gc.population, gd.code AS code_department, gcc2.code AS code_city_community, GROUP_CONCAT(gc2.code) AS code_canton
FROM enmarche.geo_city gc
    INNER JOIN enmarche.geo_department gd ON gd.id = gc.department_id
    LEFT JOIN enmarche.geo_city_canton gcc ON gcc.city_id = gc.id
    LEFT JOIN enmarche.geo_canton gc2 ON gc2.id = gcc.canton_id
    LEFT JOIN enmarche.geo_city_community gcc2 ON gcc2.id = gc.city_community_id
WHERE
    gd.code IN ('06', '13', '59', '69', '75', '76', '77', '92', '93', '94') AND
    gc.population > 2500
GROUP BY 1, 2, 3, 4, 5, 6
;
-- 10-borough.csv
SELECT gb.code, gb.name, gb.postal_code, gb.population, gc.code AS code_city
FROM enmarche.geo_borough gb
    INNER JOIN enmarche.geo_city gc ON gc.id = gb.city_id
;
