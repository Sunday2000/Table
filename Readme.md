Class Table help to easly make table and dynamically.
For this we use bootstrap 4.

Here is an example of how use it:

$pdo = Construct your \PDO instance

$query = (new QueryBuilder($pdo))->from('Your table name');

NB: You necessarly need to construct the QueryBuilder with a valid \PDO instance,
learn more about QueryBuilder here: https://github.com/­Sunday2000/­QueryBuilder

NB: For the next, "attribute" is your table attribute name.

$table = (new Table($query, $_GET))
    ->sortable('attribute_1', 'attribute_2', 'attribute_3',...., attribute_n) //Specify sortable attribute,it's can sort by asc or desc direction.
    ->format('attribute to format', function ($value){
        // Instructions to format it
        return (formated value);
    })
    ->columns([
        'attribute_1' => 'attribute_1 name in table',
        'attribute_2' => 'attribute_2 name in tble',
        'attribute_3' => 'attribute_3 name in tble',
        'attribute_1' => 'attribute_4 name in tble'
    ]) // Specify your table columns name like the following ['attribute' => 'Name in table'];

call render() function to show table on your page like the following:

<?= $table->render() ?>