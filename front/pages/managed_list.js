import { createRoot } from 'react-dom/client';
import SearchEngine from '../services/datagrid/SearchEngine';
import DataGridFactory from '../services/datagrid/DataGridFactory';

/*
 * List of items (for example, committees or events) managed by an adherent
 * with a specific role (for example, referent or deputy).
 */
export default (columns, items) => {
    const dataGridFactory = new DataGridFactory(new SearchEngine());
    const dataGrid = dataGridFactory.createDataGrid(columns, items, 50, null, 'managed__list', 'name');

    createRoot(dom('#datagrid')).render(dataGrid);
};
