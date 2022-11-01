# 類人猿デバッグヘルパー
類人猿のプロダクトのデバッグをより楽にできるように補佐機能を実装しています

## 汎用的なデバッグ用の機能
### error_logを任意のファイルに出力する機能
通常はphpのエラーログはサーバーの設定で決められたファイルに出力されますが、それだと自分が知りたいログだけ見るなどができないため（特にwp-env環境だと追いにくい）任意のファイルに出力するためのクラスを用意しています

```
$rje_log = new \Ruijinen\DebugHelper\Debug\Output_error_log(); //エラーログ出力用のclassを宣言
$rje_log->file = __DIR__ . '/fugahoge/.error_log'; //任意：出力ファイルのパス（デフォルトは {このプラグインのディレクトリ}/App/Debug/error_log）
$rje_log->reset = true; //任意：ファイルを空にしてから出力するか（デフォルトは false）
$rje_log->output_error_log({出力したい内容}); //以降はこのメソッドで呼び出し何度も可能

```

### 指定のフック名にどんな関数・メソッドがかけられているかリスト表示する機能
フックにどのような関数がかけられるか、また自分のかけた関数がちゃんとかかっているかの確認のためにリスト表示する機能を用意しています

#### ショートコードでリストを表示
`[list_filter_from_hook hook_name="{フック名}"]` でショートコード挿入箇所にリストが表示されます。（ただしショートコード表示より後に動くフックは表示されません、その場合はerror_logで表示する方法でリストを確認ください

#### error_logでリストを表示
クラスのメソッドを実行することにより、error_logファイルにリストが出力されます

```
$debug = new \Ruijinen\DebugHelper\Debug\ViewListFilterFromHook();
$debug->error_log_list_filter('{フック名}');
```
### スペーサーブロックの可視化
編集画面にてCoreのスペーサーブロックを可視化します

## Snow Monkey関連のデバッグ用の機能
### ページ上部のセレクトボックスで表示レイアウトを切り替え（Snow Monkeyテーマ有効時）
管理者権限のユーザーがログインしてるときは各ページにて上部にレイアウト切り替え用のセレクトボックスが表示されます。
セレクトボックスでレイアウトを選択すると、選択レイアウトでページが表示されます
`layout={レイアウト名}`をURLのパラメータに付与する形でも指定のレイアウト表示が可能です。

#### レイアウト名
- ランディングページ（ヘッダー・フッターあり）： blank-content
- ランディングページ（スリム幅）： blank-slim
- ランディングページ： blank
- フル幅： one-column-full
- 1カラム： one-column
- 1カラム（スリム幅）： one-column-slim
- 左サイドバー： left-sidebar
- 右サイドバー： right-sidebar

### URLパラメータ付与によるヘッダーレイアウト変更（Snow Monkeyテーマ有効時）
`header-layout={レイアウト名}`をURLのパラメータに付与すると指定のヘッダーレイアウトで表示することができます

#### レイアウト名
- 1行： 1row
- 2行： 2row
- 中央ロゴ： center
- 左： left
- シンプル： simple

### URLパラメータ付与によるSnow Monkey Editorアニメーションの無効化
`sme_animation=stop`をURLのパラメータに付与するとSnow Monkey Editorで指定したアニメーションが無効になります
（ビジュアルリグニッションテストするときに便利です）


# 変更履歴
## 0.0.6
- 指定フック名の関数一覧出力リスト機能の修正

## 0.0.5
- error_log出力機能の一部修正（メソッド化と一部引数をプロパティに変更）
- Snow Monkeyかどうかの判定が間違ってたので修正
## 0.0.4
- README.mdファイルに機能説明を追加
- 汎用的なデバッグ機能についてはSnow Monkeyテーマなくても動くように全体的なアクティベートチェックを削除
- 指定フック名の関数一覧出力リスト機能の追加
## 0.0.3
- erorr_logの出力内容を任意の場所に出力できるクラスを追加
## 0.0.2
- nodeパッケージのアップグレード
- マージせずにcloseした場合にはGitHub Acionsをスキップするように変更
## 0.0.1
- gulpからdart sassに変更
- wp-env環境の追加
- リリース機能の追加
- 各ファイルをクラスにした
## 0.0.0.4
- エディター画面にて、フォーカスが当たってない場合にその空白がスペーサーブロックによるものなのか、別の要因なのかが判別しにくかったので、スペーサーブロックにもれなく斜線背景が当たるようにCSSを追加
## 0.0.0.3
-  URLにパラメータ「sme_animation=stop」がある場合には最後に「Stoped Snow Monkey Editor Animations.」をconsole.logに表示するように修正
## 0.0.0.2
- URLにパラメータ「sme_animation=stop」がある場合はSnow Monkey Editorのアニメーションを実行しないようにする機能を追加
## 0.0.0.1
- ページレイアウト切り替え機能の追加
