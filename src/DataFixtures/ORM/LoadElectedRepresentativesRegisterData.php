<?php

namespace App\DataFixtures\ORM;

use App\Entity\ElectedRepresentativesRegister;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadElectedRepresentativesRegisterData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $elus = [
            [1, 1, 'adherent-1', 'conseiller_municipal', '01', 'AIN', 'DUFOUR', 'Michelle', 'female', '1972-11-23', 10, 'Artisans', '2014-03-23', null, null, 'NC', 1203084, 'Française', 1, 'L\'Abergement-Clémenciat', 780, '698be483b9bee9dc1eb72ff40cd18067'],
            [1, 1, null, 'conseiller_municipal', '01', 'AIN', 'BOUILLOUX', 'Delphine', 'female', '1977-08-02', 2, 'Salariés agricoles', '2014-03-23', null, null, 'NC', 1203080, 'Française', 1, 'L\'Abergement-Clémenciat', 780, '967d13cb5074d0752a39b39b3078f83a'],
            [1, 1, null, 'conseiller_municipal', '01', 'AIN', 'BOULON', 'Daniel', 'male', '1951-03-04', 61, 'Retraités salariés privés', '2014-03-23', 'Maire', '2014-03-23', 'DIV', 694516, 'Française', 1, 'L\'Abergement-Clémenciat', 780, '879a745996db861ed36af1016b74d063'],
            [1, 1, null, 'conseiller_municipal', '01', 'AIN', 'BUET', 'Roger', 'male', '1952-04-21', 1, 'Agriculteurs propriétaires exploit.', '2014-03-23', 'Troisième adjoint au maire', '2014-03-23', 'DIV', 873399, 'Française', 1, 'L\'Abergement-Clémenciat', 780, '3fe0efa5ce50c6eb76a804cffa536b61'],
            [1, 1, null, 'conseiller_municipal', '01', 'AIN', 'LOBELL', 'André', 'male', '1951-11-03', 62, 'Retraités de l\'enseignement', '2014-03-23', 'Second adjoint au maire', '2014-03-23', 'DIV', 873404, 'Française', 1, 'L\'Abergement-Clémenciat', 780, '73ea9b609b9e10db8acf74a0ba3b7811'],
        ];

        foreach ($elus as $data) {
            $electedRepresentativesRegister = new ElectedRepresentativesRegister();
            $electedRepresentativesRegister->setDepartmentId($data[0]);
            $electedRepresentativesRegister->setCommuneId($data[1]);

            if (null !== $data[2]) {
                $electedRepresentativesRegister->setAdherent($this->getReference($data[2]));
            }

            $electedRepresentativesRegister->setTypeElu($data[3]);
            $electedRepresentativesRegister->setDpt($data[4]);
            $electedRepresentativesRegister->setDptNom($data[5]);
            $electedRepresentativesRegister->setNom($data[6]);
            $electedRepresentativesRegister->setPrenom($data[7]);
            $electedRepresentativesRegister->setGenre($data[8]);
            $electedRepresentativesRegister->setDateNaissance(new \DateTime($data[9]));
            $electedRepresentativesRegister->setCodeProfession($data[10]);
            $electedRepresentativesRegister->setNomProfession($data[11]);
            $electedRepresentativesRegister->setDateDebutMandat($data[12]);
            $electedRepresentativesRegister->setNomFonction($data[13]);
            $electedRepresentativesRegister->setDateDebutFonction(new \DateTime($data[14]));
            $electedRepresentativesRegister->setNuancePolitique($data[15]);
            $electedRepresentativesRegister->setIdentificationElu($data[16]);
            $electedRepresentativesRegister->setNationaliteElu($data[17]);
            $electedRepresentativesRegister->setCommuneCode($data[18]);
            $electedRepresentativesRegister->setCommuneNom($data[19]);
            $electedRepresentativesRegister->setCommunePopulation($data[20]);
            $electedRepresentativesRegister->setUuid($data[21]);

            $manager->persist($electedRepresentativesRegister);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
