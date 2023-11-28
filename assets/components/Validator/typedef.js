/** @typedef {'error'|'warning'|'valid'|'info'|'default'} ValidateStatus */
/** @typedef {(state: NotifyState)=>void} SetNotifyState */
/** @typedef {(value:any, cb:SetNotifyState)=>ValidateTuple} ValidateCallback */
/** @typedef {'required'|'email'|'number'|'min'|'max'|ValidateCallback} ValidateType */
/** @typedef {{
 * status:ValidateStatus,
 * message:string,
 * }} NotifyState */
/** @typedef {{
 * status:ValidateStatus,
 * message:string,
 * validate:ValidateType,
 * onCheck?: (x:boolean)=>void
 * }} ValidateState */
/** @typedef {[ValidateType, ValidateState]} ValidateTuple */
