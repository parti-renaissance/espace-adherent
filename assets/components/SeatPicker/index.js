/* eslint-disable react/prop-types */
import React, { useEffect, useRef, useState } from 'react';
import { TransformWrapper, TransformComponent } from 'react-zoom-pan-pinch';

import './SeatPicker.css';

const Tooltip = ({ tooltip }) => {
    if (!tooltip) return null;

    return (
        <div
            className="tooltip"
            style={{
                left: `${tooltip.x}px`,
                top: `${tooltip.y}px`,
            }}
        >
            <div className="tooltip-line1">
                Rangée {tooltip.row} - Place {tooltip.seatNumber}
            </div>
            <div
                className="tooltip-line2"
                style={{
                    color: tooltip.isReserved ? '#FF3333' : '#3FBA61',
                }}
            >
                {tooltip.isReserved ? 'RÉSERVÉE' : 'DISPONIBLE'}
            </div>
        </div>
    );
};

const ZoomControls = ({ zoomIn, zoomOut, resetTransform }) => {
    return (
        <div className="zoom-controls">
            <button
                onClick={(e) => {
                    e.preventDefault();
                    zoomIn();
                }}
                className="zoom-control-btn"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    <line x1="11" y1="8" x2="11" y2="14"></line>
                    <line x1="8" y1="11" x2="14" y2="11"></line>
                </svg>
            </button>
            <button
                onClick={(e) => {
                    e.preventDefault();
                    zoomOut();
                }}
                className="zoom-control-btn"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    <line x1="8" y1="11" x2="14" y2="11"></line>
                </svg>
            </button>
            <button
                onClick={(e) => {
                    e.preventDefault();
                    resetTransform();
                }}
                className="zoom-control-btn"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                    <path d="M3 3v5h5"></path>
                </svg>
            </button>
        </div>
    );
};

const Seat = ({ seatId, cx, cy, isDisabled, getSeatColor, onClick, onMouseEnter, onMouseLeave }) => {
    const size = 15;
    const x = cx - size / 2;
    const y = cy - size / 2;

    return (
        <g>
            <rect
                x={x}
                y={y}
                width={size}
                height={size}
                fill="transparent"
                onClick={() => onClick(seatId)}
                onMouseEnter={(e) => onMouseEnter(seatId, e)}
                onMouseLeave={onMouseLeave}
                className={isDisabled ? 'seat-disabled-hitbox' : 'seat-hitbox'}
                pointerEvents="all"
            />
            <circle cx={cx} cy={cy} r="6" fill={getSeatColor(seatId)} data-place={seatId} pointerEvents="none" />
        </g>
    );
};

const SeatRow = ({ rowLetter, seats, y, positions, isSeatDisabled, ...seatProps }) => {
    return (
        <g data-rang={rowLetter}>
            {seats.map((seat, idx) => (
                <Seat key={seat} seatId={seat} cx={positions[idx]} cy={y} isDisabled={isSeatDisabled(seat)} {...seatProps} />
            ))}
        </g>
    );
};

const SeatMap = ({ rowLabels, rowsData, ...seatProps }) => {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1073 464" fill="none" className="seat-map-svg" preserveAspectRatio="xMidYMid meet">
            <rect x="412" y="385" width="300" height="42" fill="#D9D9D9" />

            {rowLabels.map(({ letter, y, firstCircleX }) => (
                <text
                    key={`label-${letter}`}
                    x={firstCircleX - 16}
                    y={y + 1}
                    fill="#000000"
                    fontSize="11"
                    fontFamily="Arial, sans-serif"
                    textAnchor="middle"
                    dominantBaseline="middle"
                >
                    {letter}
                </text>
            ))}

            {rowsData.map(({ rowLetter, seats, y, positions }) => (
                <SeatRow key={rowLetter} rowLetter={rowLetter} seats={seats} y={y} positions={positions} {...seatProps} />
            ))}
        </svg>
    );
};

const SeatPicker = ({ disabledSeats, initialSelectedSeat, onChange }) => {
    const [hoveredSeat, setHoveredSeat] = useState(null);
    const [tooltip, setTooltip] = useState(null);

    const [selectedSeat, setSelectedSeat] = useState(initialSelectedSeat);
    const containerRef = useRef(null);

    useEffect(() => {
        const container = containerRef.current;
        if (!container) {
            return;
        }

        const handleExternalUpdate = (event) => setSelectedSeat(event.detail.value);

        container.addEventListener('update-seat-selection', handleExternalUpdate);

        return () => container.removeEventListener('update-seat-selection', handleExternalUpdate);
    }, []);

    const isSeatDisabled = (seatId) => {
        return disabledSeats.includes(seatId);
    };

    const getSeatState = (seatId) => {
        if (selectedSeat === seatId) return 'selected';
        if (isSeatDisabled(seatId)) return 'reserved';
        return 'free';
    };

    const getSeatColor = (seatId) => {
        const state = getSeatState(seatId);

        // Si le siège est survolé directement, il est en bleu
        if (hoveredSeat === seatId && 'free' === state) {
            return '#0084FF';
        }

        switch (state) {
            case 'selected':
                return '#3FBA61';
            case 'reserved':
                return '#CBD5E1';
            default:
                return '#637381';
        }
    };

    const getRowId = (seatId) => {
        return seatId.charAt(0);
    };

    const handleSeatClick = (seatId) => {
        if (isSeatDisabled(seatId)) return;

        const newSelection = selectedSeat === seatId ? null : seatId;
        setSelectedSeat(newSelection);

        if (onChange) {
            onChange(newSelection);
        }
    };

    const handleSeatMouseEnter = (seatId, event) => {
        setHoveredSeat(seatId);

        const rect = event.currentTarget.getBoundingClientRect();
        const row = getRowId(seatId);
        const seatNumber = seatId.substring(1);
        const isReserved = isSeatDisabled(seatId);

        setTooltip({
            seatId,
            row,
            seatNumber,
            isReserved,
            x: rect.left + rect.width / 2,
            y: rect.top,
        });
    };

    const handleSeatMouseLeave = () => {
        setHoveredSeat(null);
        setTooltip(null);
    };

    // Configuration des rangées : coordonnées Y, X du premier cercle, sièges et positions
    const rowLabels = [
        { letter: 'A', y: 356, firstCircleX: 420 },
        { letter: 'B', y: 336, firstCircleX: 360 },
        { letter: 'C', y: 316, firstCircleX: 353 },
        { letter: 'D', y: 296, firstCircleX: 308 },
        { letter: 'E', y: 276, firstCircleX: 286 },
        { letter: 'F', y: 236, firstCircleX: 249 },
        { letter: 'G', y: 216, firstCircleX: 219 },
        { letter: 'H', y: 196, firstCircleX: 189 },
        { letter: 'I', y: 176, firstCircleX: 159 },
        { letter: 'J', y: 156, firstCircleX: 129 },
        { letter: 'K', y: 136, firstCircleX: 129 },
        { letter: 'L', y: 116, firstCircleX: 99 },
        { letter: 'M', y: 96, firstCircleX: 69 },
        { letter: 'N', y: 76, firstCircleX: 54 },
        { letter: 'O', y: 56, firstCircleX: 69 },
        { letter: 'P', y: 36, firstCircleX: 819 },
    ];

    return (
        <div className="seat-picker-container" ref={containerRef}>
            <TransformWrapper initialScale={1} minScale={0.7} maxScale={4} centerOnInit={true}>
                {({ zoomIn, zoomOut, resetTransform }) => (
                    <>
                        <TransformComponent wrapperClass="zoom-wrapper" contentClass="zoom-content">
                            <SeatMap
                                rowLabels={rowLabels}
                                rowsData={rowsData}
                                isSeatDisabled={isSeatDisabled}
                                getSeatColor={getSeatColor}
                                onClick={handleSeatClick}
                                onMouseEnter={handleSeatMouseEnter}
                                onMouseLeave={handleSeatMouseLeave}
                            />
                        </TransformComponent>

                        <ZoomControls zoomIn={zoomIn} zoomOut={zoomOut} resetTransform={resetTransform} />
                    </>
                )}
            </TransformWrapper>

            <Tooltip tooltip={tooltip} />
        </div>
    );
};

export default SeatPicker;

const rowsData = [
    {
        rowLetter: 'A',
        seats: ['A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'A9', 'A10', 'A11', 'A12', 'A13', 'A14', 'A15', 'A16'],
        y: 356,
        positions: [420, 435, 450, 495, 510, 525, 540, 555, 570, 585, 600, 615, 660, 675, 690, 705],
        firstCircleX: 420,
    },
    {
        rowLetter: 'B',
        seats: ['B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7', 'B8', 'B9', 'B10', 'B11', 'B12', 'B13', 'B14', 'B15', 'B16', 'B17', 'B18', 'B19', 'B20', 'B21', 'B22'],
        y: 336,
        positions: [360, 375, 390, 405, 420, 435, 480, 495, 510, 525, 540, 555, 570, 585, 600, 615, 630, 675, 690, 705, 720, 735],
        firstCircleX: 360,
    },
    {
        rowLetter: 'C',
        seats: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9', 'C10', 'C11', 'C12', 'C13', 'C14', 'C15', 'C16', 'C17', 'C18', 'C19', 'C20', 'C21', 'C22', 'C23'],
        y: 316,
        positions: [353, 368, 383, 398, 413, 428, 473, 488, 503, 518, 533, 548, 563, 578, 593, 608, 623, 638, 683, 698, 713, 728, 743],
        firstCircleX: 353,
    },
    {
        rowLetter: 'D',
        seats: [
            'D1',
            'D2',
            'D3',
            'D4',
            'D5',
            'D6',
            'D7',
            'D8',
            'D9',
            'D10',
            'D11',
            'D12',
            'D13',
            'D14',
            'D15',
            'D16',
            'D17',
            'D18',
            'D19',
            'D20',
            'D21',
            'D22',
            'D23',
            'D24',
            'D25',
            'D26',
            'D27',
        ],
        y: 296,
        positions: [308, 323, 338, 353, 368, 383, 398, 413, 458, 473, 488, 503, 518, 533, 548, 563, 578, 593, 608, 623, 638, 653, 698, 713, 728, 743, 758],
        firstCircleX: 308,
    },
    {
        rowLetter: 'E',
        seats: [
            'E1',
            'E2',
            'E3',
            'E4',
            'E5',
            'E6',
            'E7',
            'E8',
            'E9',
            'E10',
            'E11',
            'E12',
            'E13',
            'E14',
            'E15',
            'E16',
            'E17',
            'E18',
            'E19',
            'E20',
            'E21',
            'E22',
            'E23',
            'E24',
            'E25',
            'E26',
            'E27',
            'E28',
            'E29',
        ],
        y: 276,
        positions: [286, 301, 316, 331, 346, 361, 376, 391, 406, 451, 466, 481, 496, 511, 526, 541, 556, 571, 586, 601, 616, 631, 646, 661, 706, 721, 736, 751, 766],
        firstCircleX: 286,
    },
    {
        rowLetter: 'F',
        seats: [
            'F1',
            'F2',
            'F3',
            'F4',
            'F5',
            'F6',
            'F7',
            'F8',
            'F9',
            'F10',
            'F11',
            'F12',
            'F13',
            'F14',
            'F15',
            'F16',
            'F17',
            'F18',
            'F19',
            'F20',
            'F21',
            'F22',
            'F23',
            'F24',
            'F25',
            'F26',
            'F27',
            'F28',
            'F29',
            'F30',
            'F31',
            'F32',
            'F33',
        ],
        y: 236,
        positions: [
            249, 264, 279, 294, 309, 324, 339, 354, 369, 384, 429, 444, 459, 474, 489, 504, 519, 534, 579, 594, 609, 624, 639, 654, 669, 684, 729, 744, 759, 774, 789, 804, 819,
        ],
        firstCircleX: 249,
    },
    {
        rowLetter: 'G',
        seats: [
            'G1',
            'G2',
            'G3',
            'G4',
            'G5',
            'G6',
            'G7',
            'G8',
            'G9',
            'G10',
            'G11',
            'G12',
            'G13',
            'G14',
            'G15',
            'G16',
            'G17',
            'G18',
            'G19',
            'G20',
            'G21',
            'G22',
            'G23',
            'G24',
            'G25',
            'G26',
            'G27',
            'G28',
            'G29',
            'G30',
            'G31',
            'G32',
            'G33',
            'G34',
            'G35',
            'G36',
        ],
        y: 216,
        positions: [
            219, 234, 249, 264, 279, 294, 309, 324, 339, 354, 369, 414, 429, 444, 459, 474, 489, 504, 519, 534, 579, 594, 609, 624, 639, 654, 669, 684, 729, 744, 759, 774, 789,
            804, 819, 834,
        ],
        firstCircleX: 219,
    },
    {
        rowLetter: 'H',
        seats: [
            'H1',
            'H2',
            'H3',
            'H4',
            'H5',
            'H6',
            'H7',
            'H8',
            'H9',
            'H10',
            'H11',
            'H12',
            'H13',
            'H14',
            'H15',
            'H16',
            'H17',
            'H18',
            'H19',
            'H20',
            'H21',
            'H22',
            'H23',
            'H24',
            'H25',
            'H26',
            'H27',
            'H28',
            'H29',
            'H30',
            'H31',
            'H32',
            'H33',
            'H34',
            'H35',
            'H36',
            'H37',
            'H38',
            'H39',
        ],
        y: 196,
        positions: [
            189, 204, 219, 234, 249, 264, 279, 294, 309, 324, 339, 354, 399, 414, 429, 444, 459, 474, 489, 504, 519, 534, 579, 594, 609, 624, 639, 654, 669, 684, 699, 744, 759,
            774, 789, 804, 819, 834, 849,
        ],
        firstCircleX: 189,
    },
    {
        rowLetter: 'I',
        seats: [
            'I1',
            'I2',
            'I3',
            'I4',
            'I5',
            'I6',
            'I7',
            'I8',
            'I9',
            'I10',
            'I11',
            'I12',
            'I13',
            'I14',
            'I15',
            'I16',
            'I17',
            'I18',
            'I19',
            'I20',
            'I21',
            'I22',
            'I23',
            'I24',
            'I25',
            'I26',
            'I27',
            'I28',
            'I29',
            'I30',
            'I31',
            'I32',
            'I33',
            'I34',
            'I35',
            'I36',
            'I37',
            'I38',
            'I39',
            'I40',
            'I41',
            'I42',
            'I43',
        ],
        y: 176,
        positions: [
            159, 174, 189, 204, 219, 234, 249, 264, 279, 294, 309, 324, 339, 384, 399, 414, 429, 444, 459, 474, 489, 504, 519, 534, 579, 594, 609, 624, 639, 654, 669, 684, 699,
            714, 759, 774, 789, 804, 819, 834, 849, 864, 879,
        ],
        firstCircleX: 159,
    },
    {
        rowLetter: 'J',
        seats: [
            'J1',
            'J2',
            'J3',
            'J4',
            'J5',
            'J6',
            'J7',
            'J8',
            'J9',
            'J10',
            'J11',
            'J12',
            'J13',
            'J14',
            'J15',
            'J16',
            'J17',
            'J18',
            'J19',
            'J20',
            'J21',
            'J22',
            'J23',
            'J24',
            'J25',
            'J26',
            'J27',
            'J28',
            'J29',
            'J30',
            'J31',
            'J32',
            'J33',
            'J34',
            'J35',
            'J36',
            'J37',
            'J38',
            'J39',
            'J40',
            'J41',
            'J42',
            'J43',
            'J44',
            'J45',
            'J46',
        ],
        y: 156,
        positions: [
            129, 144, 159, 174, 189, 204, 219, 234, 249, 264, 279, 294, 309, 324, 369, 384, 399, 414, 429, 444, 459, 474, 489, 504, 519, 534, 579, 594, 609, 624, 639, 654, 669,
            684, 699, 714, 759, 774, 789, 804, 819, 834, 849, 864, 879, 894,
        ],
        firstCircleX: 129,
    },
    {
        rowLetter: 'K',
        seats: [
            'K1',
            'K2',
            'K3',
            'K4',
            'K5',
            'K6',
            'K7',
            'K8',
            'K9',
            'K10',
            'K11',
            'K12',
            'K13',
            'K14',
            'K15',
            'K16',
            'K17',
            'K18',
            'K19',
            'K20',
            'K21',
            'K22',
            'K23',
            'K24',
            'K25',
            'K26',
            'K27',
            'K28',
            'K29',
            'K30',
            'K31',
            'K32',
            'K33',
            'K34',
            'K35',
            'K36',
            'K37',
            'K38',
            'K39',
            'K40',
            'K41',
            'K42',
            'K43',
            'K44',
            'K45',
            'K46',
            'K47',
        ],
        y: 136,
        positions: [
            129, 144, 159, 174, 189, 204, 219, 234, 249, 264, 279, 294, 309, 324, 369, 384, 399, 414, 429, 444, 459, 474, 489, 504, 519, 534, 579, 594, 609, 624, 639, 654, 669,
            684, 699, 714, 729, 774, 789, 804, 819, 834, 849, 864, 879, 894, 909,
        ],
        firstCircleX: 129,
    },
    {
        rowLetter: 'L',
        seats: [
            'L1',
            'L2',
            'L3',
            'L4',
            'L5',
            'L6',
            'L7',
            'L8',
            'L9',
            'L10',
            'L11',
            'L12',
            'L13',
            'L14',
            'L15',
            'L16',
            'L17',
            'L18',
            'L19',
            'L20',
            'L21',
            'L22',
            'L23',
            'L24',
            'L25',
            'L26',
            'L27',
            'L28',
            'L29',
            'L30',
            'L31',
            'L32',
            'L33',
            'L34',
            'L35',
            'L36',
            'L37',
            'L38',
            'L39',
            'L40',
            'L41',
            'L42',
            'L43',
            'L44',
            'L45',
            'L46',
            'L47',
            'L48',
            'L49',
            'L50',
            'L51',
        ],
        y: 116,
        positions: [
            99, 114, 129, 144, 159, 174, 189, 204, 219, 234, 249, 264, 279, 294, 309, 354, 369, 384, 399, 414, 429, 444, 459, 474, 489, 504, 519, 534, 579, 594, 609, 624, 639, 654,
            669, 684, 699, 714, 729, 744, 789, 804, 819, 834, 849, 864, 879, 894, 909, 924, 939,
        ],
        firstCircleX: 99,
    },
    {
        rowLetter: 'M',
        seats: [
            'M1',
            'M2',
            'M3',
            'M4',
            'M5',
            'M6',
            'M7',
            'M8',
            'M9',
            'M10',
            'M11',
            'M12',
            'M13',
            'M14',
            'M15',
            'M16',
            'M17',
            'M18',
            'M19',
            'M20',
            'M21',
            'M22',
            'M23',
            'M24',
            'M25',
            'M26',
            'M27',
            'M28',
            'M29',
            'M30',
            'M31',
            'M32',
            'M33',
            'M34',
            'M35',
            'M36',
            'M37',
            'M38',
            'M39',
            'M40',
            'M41',
            'M42',
            'M43',
            'M44',
            'M45',
            'M46',
            'M47',
            'M48',
            'M49',
            'M50',
            'M51',
            'M52',
            'M53',
            'M54',
            'M55',
        ],
        y: 96,
        positions: [
            69, 84, 99, 114, 129, 144, 159, 174, 189, 204, 219, 234, 249, 264, 279, 294, 339, 354, 369, 384, 399, 414, 429, 444, 459, 474, 489, 504, 519, 534, 579, 594, 609, 624,
            639, 654, 669, 684, 699, 714, 729, 744, 759, 804, 819, 834, 849, 864, 879, 894, 909, 924, 939, 954, 969,
        ],
        firstCircleX: 69,
    },
    {
        rowLetter: 'N',
        seats: [
            'N1',
            'N2',
            'N3',
            'N4',
            'N5',
            'N6',
            'N7',
            'N8',
            'N9',
            'N10',
            'N11',
            'N12',
            'N13',
            'N14',
            'N15',
            'N16',
            'N17',
            'N18',
            'N19',
            'N20',
            'N21',
            'N22',
            'N23',
            'N24',
            'N25',
            'N26',
            'N27',
            'N28',
            'N29',
            'N30',
            'N31',
            'N32',
            'N33',
            'N34',
            'N35',
            'N36',
            'N37',
            'N38',
            'N39',
            'N40',
            'N41',
            'N42',
            'N43',
            'N44',
            'N45',
            'N46',
            'N47',
            'N48',
            'N49',
            'N50',
            'N51',
            'N52',
            'N53',
            'N54',
            'N55',
            'N56',
        ],
        y: 76,
        positions: [
            54, 69, 84, 99, 114, 129, 144, 159, 174, 189, 204, 219, 234, 249, 264, 279, 324, 339, 354, 369, 384, 399, 414, 429, 444, 459, 474, 489, 504, 519, 534, 579, 594, 609,
            624, 639, 654, 669, 684, 699, 714, 729, 744, 759, 804, 819, 834, 849, 864, 879, 894, 909, 924, 939, 954, 969,
        ],
        firstCircleX: 54,
    },
    {
        rowLetter: 'O',
        seats: [
            'O1',
            'O2',
            'O3',
            'O4',
            'O5',
            'O6',
            'O7',
            'O8',
            'O9',
            'O10',
            'O11',
            'O12',
            'O13',
            'O14',
            'O15',
            'O16',
            'O17',
            'O18',
            'O19',
            'O20',
            'O21',
            'O22',
            'O23',
            'O24',
            'O25',
            'O26',
            'O27',
            'O28',
            'O29',
            'O30',
            'O31',
            'O32',
            'O33',
            'O34',
            'O35',
            'O36',
            'O37',
            'O38',
            'O39',
            'O40',
            'O41',
            'O42',
            'O43',
            'O44',
            'O45',
            'O46',
            'O47',
            'O48',
            'O49',
            'O50',
            'O51',
            'O52',
            'O53',
            'O54',
            'O55',
            'O56',
            'O57',
        ],
        y: 56,
        positions: [
            69, 84, 99, 114, 129, 144, 159, 174, 189, 204, 219, 234, 249, 264, 279, 324, 339, 354, 369, 384, 399, 414, 429, 444, 459, 474, 489, 504, 519, 534, 579, 594, 609, 624,
            639, 654, 669, 684, 699, 714, 729, 744, 759, 774, 819, 834, 849, 864, 879, 894, 909, 924, 939, 954, 969, 984, 999,
        ],
        firstCircleX: 69,
    },
    {
        rowLetter: 'P',
        seats: ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'P9', 'P10', 'P11'],
        y: 36,
        positions: [819, 834, 849, 864, 879, 894, 909, 924, 939, 954, 969],
        firstCircleX: 819,
    },
];
