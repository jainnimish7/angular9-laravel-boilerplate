import { NgModule } from '@angular/core';

import { AddOrdinalPipe } from './add-ordinal.pipe';
import { SafePipe } from './safe.pipe';
import { RemoveUnderscorePipe } from './remove-underscore.pipe';
import { RoundOffDecimalPipe } from './round-off-decimal';
import { RoundOffValuePipe } from './round-off-value';
import { SearchNamePipe } from './search-name.pipe';
import { PlayerFilterPipe } from './player-filter.pipe';
import { TruncateStringPipePipe } from './truncate-string-pipe.pipe';

@NgModule({
  declarations: [
    AddOrdinalPipe,
    PlayerFilterPipe,
    RemoveUnderscorePipe,
    RoundOffDecimalPipe,
    RoundOffValuePipe,
    SearchNamePipe,
    SafePipe,
    TruncateStringPipePipe,
  ],
  imports: [],
  exports: [
    AddOrdinalPipe, SafePipe,
    RemoveUnderscorePipe,
    RoundOffDecimalPipe,
    RoundOffValuePipe,
    SearchNamePipe,
    PlayerFilterPipe,
  ],
  providers: [
    AddOrdinalPipe,
    SafePipe,
    RemoveUnderscorePipe,
    RoundOffDecimalPipe,
    RoundOffValuePipe
  ]
})
export class PipeModule { }
