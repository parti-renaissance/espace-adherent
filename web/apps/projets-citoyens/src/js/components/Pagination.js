import React, { Component } from 'react';

const PageNumber = ({page, goTo}) => {
  let dots = [];
  if (page.dots) {
    dots.push(
      <span key={`dots-${page.page}`} className="pagination__dots">
        &middot;&middot;&middot;
      </span>
    );
  }

  if (page.current) {
    return dots.concat(
      <span key={`current-${page.page}`} className="pagination__current">
        {page.page}
      </span>
    );
  } else {
    return dots.concat(
      <button
        key={page.page}
        className="pagination__number"
        onClick={() => goTo(page.page)}>{page.page}</button>
    );
  }
}

export default class Pagination extends Component {
  getBounds(totalPages, pagesToShow, centerPage) {
    let range = Math.floor(pagesToShow / 2);

    if (totalPages < pagesToShow) {
      return {
        lower: 1,
        upper: totalPages,
        range
      };
    } else {
      let pagesToShowIsEven = pagesToShow % 2 === 0;

      return {
        lower: centerPage - (pagesToShowIsEven ? range - 1 : range),
        upper: (centerPage + range) > totalPages ? totalPages : centerPage + range,
        range
      };
    }
  }
  render() {
    let { nextPage, prevPage, goTo, total, currentPage, pageSize, pagesToShow } = this.props;
    let totalPages = Math.ceil(total / pageSize);

    let minCenterPage = Math.ceil(pagesToShow / 2);
    let centerPage = currentPage >= minCenterPage ? currentPage : minCenterPage;

    if (totalPages <= 1) {
      return null;
    }

    let bounds = this.getBounds(totalPages, pagesToShow, centerPage);
    let pages = [];
    for (let i = bounds.lower; i <= bounds.upper; i++) {
      pages.push({
        page: i,
        current: i === currentPage
      });
    }

    // only add dots if the lower boundary is more than
    // on away from the first page, i.e. is it greater than 2
    if (bounds.lower > 2) {
      pages[0].dots = true;
    }

    if (bounds.lower !== 1) {
      pages.unshift({
        page: 1,
      });
    }

    if (bounds.upper !== totalPages) {
      pages.push({
        page: totalPages,
        // only add dots if the upper boundary is more
        // than one away from the totalPages
        dots: (totalPages - bounds.upper) > 1
      });
    }


    return (
      <div className="pagination">
        {currentPage !== 1 &&
          <button className="pagination__prev" onClick={prevPage}>&lsaquo; Précédent</button>
        }

        {pages.map((page, i) => <PageNumber key={i} goTo={goTo} page={page} /> )}

        {currentPage !== totalPages.length &&
          <button className="pagination__next" onClick={nextPage}>Suivant &rsaquo;</button>
        }
      </div>
    );
  }
}
