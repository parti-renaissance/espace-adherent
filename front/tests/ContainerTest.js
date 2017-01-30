import { assert } from 'chai';
import Container from '../services/Container';

describe('Container', () => {
    const di = new Container();

    it('Parameters can be registered', () => {
        di.set('param1', 'value');
        di.set('param2', 3);
        di.set('param3', di);
        di.set('param4', { foo: 'bar' });

        assert.equal('value', di.get('param1'));
        assert.equal(3, di.get('param2'));
        assert.equal(di, di.get('param3'));
        assert.deepEqual({ foo: 'bar' }, di.get('param4'));
    });

    it('Services can be registered', () => {
        di.set('service', () => new Array([]));

        assert.instanceOf(di.get('service'), Array);
    });

    it('Services are uniquely defined', () => {
        di.set('service', () => new Array([]));

        assert.strictEqual(di.get('service'), di.get('service'));
    });

    it('Services can be checked for existence', () => {
        di.set('service', () => new Array([]));

        assert.isTrue(di.has('service'));
        assert.isFalse(di.has('invalid'));
    });

    it('Getting inexistant service throws ReferenceError', () => {
        assert.throws(() => { di.get('invalid'); }, ReferenceError);
    });

    it('Parameters and services can be injected in constructor', () => {
        const dic = new Container({
            param: 'value',
            service: () => new Array([]),
        });

        assert.isTrue(dic.has('param'));
        assert.isTrue(dic.has('service'));
        assert.instanceOf(dic.get('service'), Array);
    });

    it('Services can depend from each others', () => {
        const dic = new Container({
            param: 'value',
        });

        dic.set('foo', () => dic);
        dic.set('bar', () => new Array(dic.get('foo')));

        assert.instanceOf(dic.get('bar'), Array);
        assert.strictEqual(dic, dic.get('bar')[0]);
    });

    it('Services are lazy', () => {
        let fooCalled = false;
        let barCalled = false;

        di.set('foo', () => {
            fooCalled = true;
            return di;
        });

        di.set('bar', () => {
            barCalled = true;
            return () => {
                di.get('foo');
            };
        });

        assert.isFalse(fooCalled);
        assert.isFalse(barCalled);

        const bar = di.get('bar');

        assert.isFalse(fooCalled);
        assert.isTrue(barCalled);

        bar();

        assert.isTrue(fooCalled);
        assert.isTrue(barCalled);
    });
});
