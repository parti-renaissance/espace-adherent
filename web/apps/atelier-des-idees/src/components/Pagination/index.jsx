import PropTypes from 'prop-types';
import React, { Component } from 'react';

const PageNumber = ({ page, goTo }) => {
    const dots = [];
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
    }
    return dots.concat(
        <button
            key={page.page}
            className="pagination__number"
            onClick={() => goTo(page.page)}>{page.page}</button>
    );
};

class Pagination extends Component {
    getBounds(totalPages, pagesToShow, centerPage) {
        const range = Math.floor(pagesToShow / 2);

        if (totalPages < pagesToShow) {
            return {
                lower: 1,
                upper: totalPages,
                range,
            };
        }
        const pagesToShowIsEven = 0 === pagesToShow % 2;

        return {
            lower: centerPage - (pagesToShowIsEven ? range - 1 : range),
            upper: (centerPage + range) > totalPages ? totalPages : centerPage + range,
            range,
        };
    }
    render() {
        const { nextPage, prevPage, goTo, total, currentPage, pageSize, pagesToShow } = this.props;
        const totalPages = Math.ceil(total / pageSize);

        const minCenterPage = Math.ceil(pagesToShow / 2);
        const centerPage = currentPage >= minCenterPage ? currentPage : minCenterPage;

        if (1 <= totalPages) {
            return null;
        }

        const bounds = this.getBounds(totalPages, pagesToShow, centerPage);
        const pages = [];
        for (let i = bounds.lower; i <= bounds.upper; i += 1) {
            pages.push({
                page: i,
                current: i === currentPage,
            });
        }

        // only add dots if the lower boundary is more than
        // one away from the first page, i.e. is it greater than 2
        if (2 < bounds.lower) {
            pages[0].dots = true;
        }

        if (1 !== bounds.lower) {
            pages.unshift({
                page: 1,
            });
        }

        if (bounds.upper !== totalPages) {
            pages.push({
                page: totalPages,
                // only add dots if the upper boundary is more
                // than one away from the totalPages
                dots: 1 < (totalPages - bounds.upper),
            });
        }


        return (
            <div className="pagination">
                {1 !== currentPage &&
                  <button className="pagination__prev" onClick={prevPage}>&lsaquo; Précédent</button>
                }

                {pages.map((page, i) => <PageNumber key={i} goTo={goTo} page={page} />)}

                {currentPage !== totalPages.length &&
                  <button className="pagination__next" onClick={nextPage}>Suivant &rsaquo;</button>
                }
            </div>
        );
    }
}
