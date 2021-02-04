export default class DateFieldsSynchronizer {
    sync(referenceDateFieldName, targetDateFieldName) {
        findAll(document, `select[name^="${referenceDateFieldName}"]`).forEach((select) => {
            on(select, 'change', () => {
                const date = new Date(
                    dom(`select[name="${referenceDateFieldName}[date][year]"]`).value,
                    dom(`select[name="${referenceDateFieldName}[date][month]"]`).value - 1,
                    dom(`select[name="${referenceDateFieldName}[date][day]"]`).value,
                    dom(`select[name="${referenceDateFieldName}[time][hour]"]`).value,
                    dom(`select[name="${referenceDateFieldName}[time][minute]"]`).value
                );
                date.setHours(date.getHours() + 2);

                dom(`select[name="${targetDateFieldName}[date][year]"]`).value = date.getFullYear();
                dom(`select[name="${targetDateFieldName}[date][month]"]`).value = date.getMonth() + 1;
                dom(`select[name="${targetDateFieldName}[date][day]"]`).value = date.getDate();
                dom(`select[name="${targetDateFieldName}[time][hour]"]`).value = date.getHours();
                dom(`select[name="${targetDateFieldName}[time][minute]"]`).value = date.getMinutes();
            });
        });
    }
}
