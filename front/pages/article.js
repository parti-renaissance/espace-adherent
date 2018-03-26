export default () => {
    const socialBlock = find(document, '.article__main .article__social');
    const topOfSocialBlock = socialBlock.offsetTop;
    const heightOfSocialBlock = socialBlock.offsetHeight;
    const articleTitle = find(document, '.article__main article h1:nth-child(2)');
    const sidebar = find(document, '.article__sidebar');


    // socialBlock.style.marginLeft = (sidebar.offsetWidth /4) + 'px';
    // socialBlock.style.marginRight = (sidebar.offsetWidth /4) + 'px';

    // function fixSocialBlock(){
    //     if(window.scrollY >= topOfSocialBlock){
    //         const socialBlockFix = find(document, '.article__social.isFixe');
    //         addClass(socialBlock, 'isFixe');
    //         socialBlockFix.style.marginLeft = (socialBlock.offsetWidth / 4) + 'px';
    //         socialBlockFix.style.marginRight = (socialBlock.offsetWidth / 4) + 'px';
    //     } else{
    //         removeClass(socialBlock, 'isFixe')
    //     }
    // };
    //
    // on(document,'scroll', fixSocialBlock);
};
