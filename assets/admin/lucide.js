import { createIcons, Mail, Phone, Laptop, Smartphone } from 'lucide';

const setupLucide = () => {
    createIcons({ icons: {
        Mail,
        Phone,
        Laptop,
        Smartphone,
    } });
};

export default setupLucide;
