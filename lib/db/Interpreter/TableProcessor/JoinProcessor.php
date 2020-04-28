<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\TableProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlJoin;
use Gnf\db\Interpreter\SerializeTrait;
use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Super\TableProcessorInterface;

class JoinProcessor implements TableProcessorInterface
{
    use SerializeTrait;

    public static function isCondition($value): bool
    {
        return $value instanceof GnfSqlJoin;
    }

    /**
     * @param InterpreterProvider $interpreter_provider
     * @param GnfSqlJoin          $value
     *
     * @return string
     */
    public static function process($interpreter_provider, $value)
    {
        $ret = '';
        foreach ($value->dat as $k => $columns) {
            /** @var $has_join_only_one_column
             * if $has_join_only_one_column = true
             * => sqlJoin(array('tb_pay_info.t_id', 'tb_cash.t_id', 'tb_point.t_id'))
             * if $has_join_only_one_column = false
             * => sqljoin(array('tb_pay_info.t_id' => array('tb_cash.t_id', 'tb_point.t_id')))
             */

            $has_join_only_one_column = is_int($k);

            if (!is_array($columns)) {
                $columns = [$columns];
            }
            if ($has_join_only_one_column) {
                $last_column = '';
                foreach ($columns as $key_of_column => $column) {
                    if (strlen($ret) === 0) {
                        $ret .= EscapeHelper::escapeTableNameFromFullColumnElement($column);
                    } else {
                        $ret .=
                            "\n\t" . $value->type . ' ' . EscapeHelper::escapeTableNameFromFullColumnElement($column) .
                            "\n\t\t" . 'on ' . EscapeHelper::escapeColumnName($last_column) .
                            ' = ' . EscapeHelper::escapeColumnName($column);
                    }
                    $last_column = $column;
                }
            } else {
                /** @var $has_more_joinable_where_clause
                 * if $has_more_joinable_where_clause = true
                 *  => sqljoin(array('tb_pay_info.t_id' => array('tb_cash.t_id', 'tb_cash.type' => 'event')))
                 * if $has_more_joinable_where_clause = false
                 *  => sqljoin(array('tb_pay_info.t_id' => array('tb_cash.t_id')))
                 */

                $joinable_where_clause = [];
                foreach ($columns as $key_of_column => $column) {
                    $has_more_joinable_where_clause = !is_int($key_of_column);
                    if ($has_more_joinable_where_clause) {
                        $table_name = EscapeHelper::escapeTableNameFromFullColumnElement($key_of_column);
                        $joinable_where_clause[$table_name][$key_of_column] = $column;
                    }
                }

                foreach ($columns as $key_of_column => $column) {
                    $has_more_joinable_where_clause = !is_int($key_of_column);
                    if (!$has_more_joinable_where_clause) {
                        $join_left_column = $k;
                        $join_right_column = $column;

                        if (strlen($ret) == 0) {
                            $ret .= EscapeHelper::escapeTableNameFromFullColumnElement($join_left_column) . ' ' .
                                "\n\t" . $value->type . ' ' .
                                EscapeHelper::escapeTableNameFromFullColumnElement($join_right_column) .
                                "\n\t\t" . 'on ' .
                                EscapeHelper::escapeColumnName($join_left_column) .
                                ' = ' .
                                EscapeHelper::escapeColumnName($join_right_column);
                        } else {
                            $ret .= ' ' .
                                "\n\t" . $value->type .
                                ' ' .
                                EscapeHelper::escapeTableNameFromFullColumnElement($join_right_column) .
                                "\n\t\t" . 'on ' .
                                EscapeHelper::escapeColumnName($join_left_column) .
                                ' = ' .
                                EscapeHelper::escapeColumnName($join_right_column);
                        }
                        $join_right_table_name = EscapeHelper::escapeTableNameFromFullColumnElement($join_right_column);
                        if (isset($joinable_where_clause[$join_right_table_name])) {
                            $ret .= ' and '
                                . self::serializeWhere($interpreter_provider, $joinable_where_clause[$join_right_table_name]);
                            unset($joinable_where_clause[$join_right_table_name]);
                        }
                    }
                }
                foreach ($joinable_where_clause as $table_name => $where) {
                    $ret .= ' and ' . self::serializeWhere($interpreter_provider, $where);
                }
            }
        }

        return $ret;
    }
}
