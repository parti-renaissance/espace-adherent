import * as actionTypes from '../../constants/actionTypes';
import reducer, { initialState, getIdeas, getFinalizedIdeas } from '../../reducers/ideas';

describe('ideas reducer', () => {
    it('should return the initial state', () => {
        expect(reducer(undefined, {})).toEqual(initialState);
    });

    it('handles SET_IDEAS', () => {
        const ideas = [{ id: '000', title: 'Super duper idea' }];
        const result = reducer(initialState, { type: actionTypes.SET_IDEAS, payload: { ideas } });
        expect(result).toEqual(ideas);
    });

    it('handles ADD_IDEAS', () => {
        const initialIdeas = [{ id: '000', title: 'Super duper idea' }];
        const newIdeas = [{ id: '111', title: 'New super duper idea' }];
        const result = reducer(initialIdeas, { type: actionTypes.ADD_IDEAS, payload: { ideas: newIdeas } });
        // length is ok and contains all ideas
        expect(result).toHaveLength(initialIdeas.length + newIdeas.length);
        expect(result).toEqual(expect.arrayContaining(initialIdeas));
        expect(result).toEqual(expect.arrayContaining(newIdeas));
    });

    // getters
    describe('getIdeas', () => {
        it('returns all the ideas', () => {
            expect(getIdeas(initialState)).toEqual(initialState);
            const state = [{ id: '000', title: 'Super duper idea' }, { id: '111', title: 'An other super duper idea' }];
            expect(getIdeas(state)).toEqual(state);
        });
    });

    describe('getFinalizedIdeas', () => {
        it('returns all the ideas', () => {
            expect(getFinalizedIdeas(initialState)).toEqual(initialState);
            const state = [
                { id: '000', title: 'Super duper idea', status: 'finalized' },
                { id: '111', title: 'An other super duper idea', status: 'pending' },
            ];
            const finalizedIdeas = getFinalizedIdeas(state);
            expect(finalizedIdeas).toHaveLength(1);
            expect(finalizedIdeas).toContain(state[0]);
        });
    });
});
