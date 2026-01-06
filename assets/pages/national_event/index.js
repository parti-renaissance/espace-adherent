import React from 'react';
import ReactDom from 'react-dom/client';
import Page from './components/Page';
import SeatPicker from '../../components/SeatPicker';

export default () => {
    window.Alpine.data('xNationalEventPage', Page);

    findAll(document, '.seat-picker').forEach((el) => {
        const initialSelectedSeat = el.dataset.selectedSeat || null;
        const targetId = el.dataset.targetId;

        const handleSeatClick = (seatId) => {
            const event = new CustomEvent('seat-selected', {
                detail: { value: seatId },
                bubbles: true,
            });
            dom('#' + targetId).dispatchEvent(event);
        };

        ReactDom.createRoot(el).render(<SeatPicker disabledSeats={[]} initialSelectedSeat={initialSelectedSeat} onChange={handleSeatClick} />);
    });
};
