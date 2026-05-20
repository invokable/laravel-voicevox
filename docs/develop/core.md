## VOICEVOX コア

PHP版のFFIラッパーが作れたので他も可能な範囲で実装していく。
https://github.com/invokable/voicevox-core-php

クライアント機能は今のまま公式VOICEVOXエンジンを使う想定で開発を継続。  
Laravel版エンジンが完成したら変更するかもしれないけどHttpを経由するのが非効率だったら別の実装方法にするかもしれない。

公式エンジンを別で動かせばLaravel版クライアントはFFIなしで使えるメリットがあるので残す理由があった。

名前空間：`Revolution\Voicevox\Core`
